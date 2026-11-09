<?php

namespace App\Application\Purchasing;

use App\Application\Contracts\AuditLogger;
use App\Application\Contracts\Dates;
use App\Application\Contracts\TransactionManager;
use App\Domain\Inventory\StockRepository;
use App\Domain\Purchasing\PurchaseOrderRepository;
use App\Domain\Purchasing\PurchaseStatus;
use App\Domain\Shared\BusinessRuleException;
use App\Domain\Shared\NotFoundException;

/**
 * Barang dari supplier sudah datang: setiap barang dicatat sebagai stok masuk (batch baru),
 * lalu status pemesanan menjadi Diterima. Semua dalam satu transaksi.
 */
final class ReceivePurchaseOrder
{
    public function __construct(
        private PurchaseOrderRepository $orders,
        private StockRepository $stock,
        private TransactionManager $tx,
        private AuditLogger $audit,
    ) {
    }

    /** @param array<int|string, ?string> $expiryDates tanggal kedaluwarsa per id barang pemesanan */
    public function execute(int $id, string $receivedDate, array $expiryDates, int $actorId): void
    {
        $order = $this->orders->find($id) ?? throw NotFoundException::of('Pemesanan');
        if (! $order['status']->canReceive()) {
            throw new BusinessRuleException('Hanya pemesanan berstatus Dipesan yang bisa diterima.');
        }

        $received = Dates::required($receivedDate, 'Tanggal diterima');
        $expiry = [];
        foreach ($order['items'] as $item) {
            $date = Dates::parse($expiryDates[$item['id']] ?? null, 'Tanggal kedaluwarsa');
            if ($date !== null && $date < $received) {
                throw new BusinessRuleException('Tanggal kedaluwarsa tidak boleh sebelum tanggal diterima.');
            }
            $expiry[$item['id']] = $date;
        }

        $this->tx->run(function () use ($id, $order, $received, $expiry, $actorId) {
            foreach ($order['items'] as $item) {
                $this->stock->receive(
                    $item['raw_material_id'], $order['supplier_id'], $order['number'], $received, $expiry[$item['id']],
                    $item['quantity'], $item['unit_cost'], "Dari pemesanan {$order['number']}", $actorId,
                );
            }
            $this->orders->setStatus($id, PurchaseStatus::Received, $actorId, $received);
            $this->audit->log($actorId, 'update', 'purchase_order', $id, "Menerima barang pemesanan {$order['number']}, stok bertambah");
        });
    }
}
