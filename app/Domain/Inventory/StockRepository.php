<?php

namespace App\Domain\Inventory;

use DateTimeImmutable;

interface StockRepository
{
    /** @return StockBatch[] batch yang masih punya sisa stok */
    public function batchesWithStock(int $rawMaterialId): array;

    public function findBatch(int $batchId): ?StockBatch;

    /** Mencatat batch baru beserta pergerakan stok masuk. Mengembalikan ID batch. */
    public function receive(
        int $rawMaterialId,
        ?int $supplierId,
        ?string $batchCode,
        DateTimeImmutable $receivedDate,
        ?DateTimeImmutable $expiryDate,
        float $quantity,
        float $unitCost,
        ?string $notes,
        int $actorId,
    ): int;

    /** $newQuantity diisi hanya bila batch belum pernah dipakai. */
    public function updateBatch(
        int $batchId,
        ?int $supplierId,
        ?string $batchCode,
        DateTimeImmutable $receivedDate,
        ?DateTimeImmutable $expiryDate,
        ?float $newQuantity,
        float $unitCost,
        ?string $notes,
    ): void;

    public function deleteBatch(int $batchId): void;

    /**
     * Mengurangi sisa batch sesuai alokasi dan mencatat pergerakan stok keluar.
     *
     * @param StockAllocation[] $allocations
     */
    public function withdraw(
        int $rawMaterialId,
        array $allocations,
        StockMovementType $type,
        ?int $productionPlanId,
        ?string $notes,
        int $actorId,
    ): void;
}
