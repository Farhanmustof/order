<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Purchasing\PurchaseOrderRepository;
use App\Domain\Purchasing\PurchaseStatus;
use App\Infrastructure\Persistence\Models\PurchaseOrder;
use DateTimeImmutable;

final class EloquentPurchaseOrderRepository implements PurchaseOrderRepository
{
    public function find(int $id): ?array
    {
        $order = PurchaseOrder::with('items')->find($id);

        return $order ? [
            'number' => $order->number,
            'supplier_id' => $order->supplier_id,
            'status' => $order->status,
            'items' => $order->items->map(fn ($i) => [
                'id' => $i->id,
                'raw_material_id' => $i->raw_material_id,
                'quantity' => $i->quantity,
                'unit_cost' => $i->unit_cost,
            ])->all(),
        ] : null;
    }

    public function create(int $supplierId, DateTimeImmutable $orderDate, ?DateTimeImmutable $expectedDate, ?string $notes, array $items, int $actorId): array
    {
        $order = PurchaseOrder::create([
            'number' => DocumentNumber::next(PurchaseOrder::class, 'PB'),
            'supplier_id' => $supplierId,
            'order_date' => $orderDate->format('Y-m-d'),
            'expected_date' => $expectedDate?->format('Y-m-d'),
            'status' => PurchaseStatus::Draft,
            'total' => $this->total($items),
            'notes' => $notes,
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]);
        $order->items()->createMany($items);

        return [$order->id, $order->number];
    }

    public function update(int $id, int $supplierId, DateTimeImmutable $orderDate, ?DateTimeImmutable $expectedDate, ?string $notes, array $items, int $actorId): void
    {
        $order = PurchaseOrder::findOrFail($id);
        $order->update([
            'supplier_id' => $supplierId,
            'order_date' => $orderDate->format('Y-m-d'),
            'expected_date' => $expectedDate?->format('Y-m-d'),
            'total' => $this->total($items),
            'notes' => $notes,
            'updated_by' => $actorId,
        ]);
        $order->items()->delete();
        $order->items()->createMany($items);
    }

    public function setStatus(int $id, PurchaseStatus $status, int $actorId, ?DateTimeImmutable $receivedDate = null): void
    {
        PurchaseOrder::whereKey($id)->firstOrFail()->update([
            'status' => $status,
            'received_date' => $receivedDate?->format('Y-m-d'),
            'updated_by' => $actorId,
        ]);
    }

    public function delete(int $id): void
    {
        PurchaseOrder::findOrFail($id)->delete();
    }

    private function total(array $items): float
    {
        return array_sum(array_map(fn ($i) => $i['quantity'] * $i['unit_cost'], $items));
    }
}
