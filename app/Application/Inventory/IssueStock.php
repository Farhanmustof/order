<?php

namespace App\Application\Inventory;

use App\Application\Contracts\AuditLogger;
use App\Application\Contracts\Clock;
use App\Application\Contracts\TransactionManager;
use App\Domain\Inventory\RawMaterialRepository;
use App\Domain\Inventory\StockAllocator;
use App\Domain\Inventory\StockMovementType;
use App\Domain\Inventory\StockRepository;
use App\Domain\Shared\BusinessRuleException;
use App\Domain\Shared\NotFoundException;

/**
 * Mencatat stok keluar manual (rusak, kedaluwarsa, atau koreksi hitung).
 * Jika batch tidak dipilih, stok diambil otomatis dengan aturan FEFO.
 */
final class IssueStock
{
    public function __construct(
        private StockRepository $stock,
        private RawMaterialRepository $materials,
        private StockAllocator $allocator,
        private Clock $clock,
        private TransactionManager $tx,
        private AuditLogger $audit,
    ) {
    }

    public function execute(int $rawMaterialId, float $quantity, StockMovementType $type, ?int $batchId, ?string $notes, int $actorId): void
    {
        if (! in_array($type, StockMovementType::manualOut(), true)) {
            throw new BusinessRuleException('Jenis stok keluar tidak valid.');
        }
        if ($quantity <= 0) {
            throw new BusinessRuleException('Jumlah stok keluar harus lebih dari 0.');
        }
        $info = $this->materials->info($rawMaterialId) ?? throw NotFoundException::of('Bahan baku');

        $this->tx->run(function () use ($rawMaterialId, $quantity, $type, $batchId, $notes, $actorId, $info) {
            $batches = $this->stock->batchesWithStock($rawMaterialId);
            if ($batchId !== null) {
                $batches = array_filter($batches, fn ($b) => $b->id === $batchId);
                if ($batches === []) {
                    throw new BusinessRuleException('Batch yang dipilih tidak punya sisa stok untuk bahan ini.');
                }
            }

            // Stok kedaluwarsa ikut dihitung karena justru itulah yang biasanya dibuang.
            $allocations = $this->allocator->allocate($batches, $quantity, $this->clock->today(), $info['name'], $info['unit'], includeExpired: true);
            $this->stock->withdraw($rawMaterialId, $allocations, $type, null, $notes, $actorId);
            $this->audit->log($actorId, 'create', 'stock_movement', null, "Stok keluar ({$type->label()}) {$info['name']} sebanyak {$quantity} {$info['unit']}");
        });
    }
}
