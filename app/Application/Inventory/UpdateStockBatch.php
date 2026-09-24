<?php

namespace App\Application\Inventory;

use App\Application\Contracts\AuditLogger;
use App\Application\Contracts\TransactionManager;
use App\Domain\Inventory\StockRepository;
use App\Domain\Shared\BusinessRuleException;
use App\Domain\Shared\NotFoundException;

final class UpdateStockBatch
{
    public function __construct(
        private StockRepository $stock,
        private TransactionManager $tx,
        private AuditLogger $audit,
    ) {
    }

    public function execute(int $batchId, StockBatchInput $input, int $actorId): void
    {
        $batch = $this->stock->findBatch($batchId) ?? throw NotFoundException::of('Batch stok');
        [$received, $expiry] = $input->dates();

        if ($batch->rawMaterialId !== $input->rawMaterialId) {
            throw new BusinessRuleException('Bahan baku sebuah batch tidak bisa diganti. Hapus batch ini lalu catat ulang.');
        }

        // Jumlah hanya boleh diubah jika batch belum pernah dipakai, agar riwayat tetap konsisten.
        $newQuantity = null;
        if (abs($input->quantity - $batch->quantityInitial) > 0.0005) {
            if (! $batch->isUntouched()) {
                throw new BusinessRuleException('Jumlah tidak bisa diubah karena batch ini sudah terpakai. Gunakan "Catat stok keluar" dengan jenis Koreksi stok.');
            }
            $newQuantity = $input->quantity;
        }

        $this->tx->run(function () use ($batchId, $input, $received, $expiry, $newQuantity, $actorId) {
            $this->stock->updateBatch($batchId, $input->supplierId, $input->batchCode, $received, $expiry, $newQuantity, $input->unitCost, $input->notes);
            $this->audit->log($actorId, 'update', 'stock_batch', $batchId, 'Mengubah data batch stok');
        });
    }
}
