<?php

namespace App\Application\Inventory;

use App\Application\Contracts\AuditLogger;
use App\Application\Contracts\TransactionManager;
use App\Domain\Inventory\StockRepository;
use App\Domain\Shared\BusinessRuleException;
use App\Domain\Shared\NotFoundException;

final class DeleteStockBatch
{
    public function __construct(
        private StockRepository $stock,
        private TransactionManager $tx,
        private AuditLogger $audit,
    ) {
    }

    public function execute(int $batchId, int $actorId): void
    {
        $batch = $this->stock->findBatch($batchId) ?? throw NotFoundException::of('Batch stok');
        if (! $batch->isUntouched()) {
            throw new BusinessRuleException('Batch yang sudah terpakai tidak bisa dihapus. Catat sisa stoknya sebagai stok keluar bila perlu dibuang.');
        }

        $this->tx->run(function () use ($batchId, $actorId) {
            $this->stock->deleteBatch($batchId);
            $this->audit->log($actorId, 'delete', 'stock_batch', $batchId, 'Menghapus catatan stok masuk');
        });
    }
}
