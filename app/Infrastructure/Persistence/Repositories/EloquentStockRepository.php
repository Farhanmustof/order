<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Inventory\StockAllocation;
use App\Domain\Inventory\StockBatch;
use App\Domain\Inventory\StockMovementType;
use App\Domain\Inventory\StockRepository;
use App\Infrastructure\Persistence\Models\StockBatch as BatchModel;
use App\Infrastructure\Persistence\Models\StockMovement;
use DateTimeImmutable;

final class EloquentStockRepository implements StockRepository
{
    public function batchesWithStock(int $rawMaterialId): array
    {
        return BatchModel::where('raw_material_id', $rawMaterialId)
            ->where('quantity_remaining', '>', 0)
            ->lockForUpdate()
            ->get()
            ->map(fn (BatchModel $b) => $this->toDomain($b))
            ->all();
    }

    public function findBatch(int $batchId): ?StockBatch
    {
        $model = BatchModel::find($batchId);

        return $model ? $this->toDomain($model) : null;
    }

    public function receive(
        int $rawMaterialId, ?int $supplierId, ?string $batchCode, DateTimeImmutable $receivedDate,
        ?DateTimeImmutable $expiryDate, float $quantity, float $unitCost, ?string $notes, int $actorId,
    ): int {
        $batch = BatchModel::create([
            'raw_material_id' => $rawMaterialId,
            'supplier_id' => $supplierId,
            'batch_code' => $batchCode,
            'received_date' => $receivedDate->format('Y-m-d'),
            'expiry_date' => $expiryDate?->format('Y-m-d'),
            'quantity_initial' => $quantity,
            'quantity_remaining' => $quantity,
            'unit_cost' => $unitCost,
            'notes' => $notes,
            'created_by' => $actorId,
        ]);

        StockMovement::create([
            'raw_material_id' => $rawMaterialId,
            'stock_batch_id' => $batch->id,
            'type' => StockMovementType::In,
            'quantity' => $quantity,
            'notes' => $notes,
            'created_by' => $actorId,
        ]);

        return $batch->id;
    }

    public function updateBatch(
        int $batchId, ?int $supplierId, ?string $batchCode, DateTimeImmutable $receivedDate,
        ?DateTimeImmutable $expiryDate, ?float $newQuantity, float $unitCost, ?string $notes,
    ): void {
        $batch = BatchModel::findOrFail($batchId);
        $batch->fill([
            'supplier_id' => $supplierId,
            'batch_code' => $batchCode,
            'received_date' => $receivedDate->format('Y-m-d'),
            'expiry_date' => $expiryDate?->format('Y-m-d'),
            'unit_cost' => $unitCost,
            'notes' => $notes,
        ]);

        if ($newQuantity !== null) {
            $batch->quantity_initial = $newQuantity;
            $batch->quantity_remaining = $newQuantity;
            StockMovement::where('stock_batch_id', $batchId)
                ->where('type', StockMovementType::In->value)
                ->update(['quantity' => $newQuantity]);
        }

        $batch->save();
    }

    public function deleteBatch(int $batchId): void
    {
        BatchModel::findOrFail($batchId)->delete();
    }

    public function withdraw(
        int $rawMaterialId, array $allocations, StockMovementType $type,
        ?int $productionPlanId, ?string $notes, int $actorId,
    ): void {
        foreach ($allocations as $allocation) {
            /** @var StockAllocation $allocation */
            $batch = BatchModel::lockForUpdate()->findOrFail($allocation->batchId);
            $batch->quantity_remaining = max(0, round($batch->quantity_remaining - $allocation->quantity, 3));
            $batch->save();

            StockMovement::create([
                'raw_material_id' => $rawMaterialId,
                'stock_batch_id' => $batch->id,
                'production_plan_id' => $productionPlanId,
                'type' => $type,
                'quantity' => $allocation->quantity,
                'notes' => $notes,
                'created_by' => $actorId,
            ]);
        }
    }

    private function toDomain(BatchModel $b): StockBatch
    {
        return new StockBatch(
            $b->id,
            $b->raw_material_id,
            DateTimeImmutable::createFromInterface($b->received_date),
            $b->expiry_date ? DateTimeImmutable::createFromInterface($b->expiry_date) : null,
            $b->quantity_initial,
            $b->quantity_remaining,
        );
    }
}
