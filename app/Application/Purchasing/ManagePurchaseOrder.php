<?php

namespace App\Application\Purchasing;

use App\Application\Contracts\AuditLogger;
use App\Application\Contracts\Dates;
use App\Application\Contracts\TransactionManager;
use App\Domain\Purchasing\PurchaseOrderRepository;
use App\Domain\Purchasing\PurchaseStatus;
use App\Domain\Shared\BusinessRuleException;
use App\Domain\Shared\NotFoundException;

/** Membuat, mengubah, memesan, membatalkan, dan menghapus pemesanan ke supplier. */
final class ManagePurchaseOrder
{
    public function __construct(
        private PurchaseOrderRepository $orders,
        private TransactionManager $tx,
        private AuditLogger $audit,
    ) {
    }

    /** @param array{supplier_id: int|string, order_date: string, expected_date?: ?string, notes?: ?string, items: array} $data */
    public function create(array $data, int $actorId): int
    {
        [$orderDate, $expectedDate, $items] = $this->check($data);

        return $this->tx->run(function () use ($data, $orderDate, $expectedDate, $items, $actorId) {
            [$id, $number] = $this->orders->create((int) $data['supplier_id'], $orderDate, $expectedDate, $data['notes'] ?? null, $items, $actorId);
            $this->audit->log($actorId, 'create', 'purchase_order', $id, "Membuat pemesanan {$number}");

            return $id;
        });
    }

    public function update(int $id, array $data, int $actorId): void
    {
        $order = $this->get($id);
        if (! $order['status']->isEditable()) {
            throw new BusinessRuleException('Pemesanan yang sudah dipesan tidak bisa diubah lagi.');
        }
        [$orderDate, $expectedDate, $items] = $this->check($data);

        $this->tx->run(function () use ($id, $data, $orderDate, $expectedDate, $items, $actorId, $order) {
            $this->orders->update($id, (int) $data['supplier_id'], $orderDate, $expectedDate, $data['notes'] ?? null, $items, $actorId);
            $this->audit->log($actorId, 'update', 'purchase_order', $id, "Mengubah pemesanan {$order['number']}");
        });
    }

    /** Draft → Dipesan: pesanan sudah dikirim ke supplier. */
    public function markOrdered(int $id, int $actorId): void
    {
        $order = $this->get($id);
        if (! $order['status']->canOrder()) {
            throw new BusinessRuleException('Hanya pemesanan berstatus Draft yang bisa ditandai dipesan.');
        }
        $this->tx->run(function () use ($id, $order, $actorId) {
            $this->orders->setStatus($id, PurchaseStatus::Ordered, $actorId);
            $this->audit->log($actorId, 'update', 'purchase_order', $id, "Pemesanan {$order['number']} dikirim ke supplier");
        });
    }

    public function cancel(int $id, int $actorId): void
    {
        $order = $this->get($id);
        if (! $order['status']->canCancel()) {
            throw new BusinessRuleException('Pemesanan yang sudah diterima tidak bisa dibatalkan.');
        }
        $this->tx->run(function () use ($id, $order, $actorId) {
            $this->orders->setStatus($id, PurchaseStatus::Cancelled, $actorId);
            $this->audit->log($actorId, 'update', 'purchase_order', $id, "Membatalkan pemesanan {$order['number']}");
        });
    }

    public function delete(int $id, int $actorId): void
    {
        $order = $this->get($id);
        if (! $order['status']->isDeletable()) {
            throw new BusinessRuleException('Hanya pemesanan Draft atau Dibatalkan yang bisa dihapus.');
        }
        $this->tx->run(function () use ($id, $order, $actorId) {
            $this->orders->delete($id);
            $this->audit->log($actorId, 'delete', 'purchase_order', $id, "Menghapus pemesanan {$order['number']}");
        });
    }

    private function get(int $id): array
    {
        return $this->orders->find($id) ?? throw NotFoundException::of('Pemesanan');
    }

    /** Memeriksa tanggal dan daftar barang. */
    private function check(array $data): array
    {
        $orderDate = Dates::required($data['order_date'] ?? null, 'Tanggal pesan');
        $expectedDate = Dates::parse($data['expected_date'] ?? null, 'Perkiraan datang');
        if ($expectedDate !== null && $expectedDate < $orderDate) {
            throw new BusinessRuleException('Perkiraan datang tidak boleh sebelum tanggal pesan.');
        }

        $items = [];
        foreach ($data['items'] ?? [] as $row) {
            $materialId = (int) ($row['raw_material_id'] ?? 0);
            $quantity = (float) ($row['quantity'] ?? 0);
            $cost = (float) ($row['unit_cost'] ?? 0);
            if ($quantity <= 0) {
                throw new BusinessRuleException('Jumlah setiap barang harus lebih dari 0.');
            }
            if ($cost < 0) {
                throw new BusinessRuleException('Harga beli tidak boleh negatif.');
            }
            if (isset($items[$materialId])) {
                throw new BusinessRuleException('Bahan yang sama tidak boleh dimasukkan dua kali.');
            }
            $items[$materialId] = ['raw_material_id' => $materialId, 'quantity' => $quantity, 'unit_cost' => $cost];
        }
        if (! $items) {
            throw new BusinessRuleException('Pemesanan harus berisi minimal satu barang.');
        }

        return [$orderDate, $expectedDate, array_values($items)];
    }
}
