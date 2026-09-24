<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\PreOrder\PreOrder;
use App\Domain\PreOrder\PreOrderItem;
use App\Domain\PreOrder\PreOrderRepository;
use App\Domain\Production\ProductionStatus;
use App\Infrastructure\Persistence\Models\PreOrder as PreOrderModel;
use App\Infrastructure\Persistence\Models\ProductionPlanItem;
use DateTimeImmutable;

final class EloquentPreOrderRepository implements PreOrderRepository
{
    public function find(int $id): ?PreOrder
    {
        $model = PreOrderModel::with('items')->find($id);
        if ($model === null) {
            return null;
        }

        return PreOrder::restore(
            $model->id,
            $model->customer_name,
            $model->customer_phone,
            $model->customer_address,
            DateTimeImmutable::createFromInterface($model->order_date),
            DateTimeImmutable::createFromInterface($model->due_date),
            $model->status,
            $model->down_payment,
            $model->notes,
            $model->items->map(fn ($i) => new PreOrderItem($i->product_id, $i->quantity, $i->unit_price))->all(),
        );
    }

    public function save(PreOrder $order, int $actorId): int
    {
        $model = $order->id ? PreOrderModel::findOrFail($order->id) : new PreOrderModel([
            'number' => DocumentNumber::next(PreOrderModel::class, 'PO'),
            'created_by' => $actorId,
        ]);

        $model->fill([
            'customer_name' => $order->customerName(),
            'customer_phone' => $order->customerPhone(),
            'customer_address' => $order->customerAddress(),
            'order_date' => $order->orderDate()->format('Y-m-d'),
            'due_date' => $order->dueDate()->format('Y-m-d'),
            'status' => $order->status(),
            'down_payment' => $order->downPayment(),
            'total' => $order->total(),
            'notes' => $order->notes(),
            'updated_by' => $actorId,
        ])->save();

        $model->items()->delete();
        $model->items()->createMany(array_map(fn (PreOrderItem $i) => [
            'product_id' => $i->productId,
            'quantity' => $i->quantity,
            'unit_price' => $i->unitPrice,
        ], $order->items()));

        return $model->id;
    }

    public function delete(int $id): void
    {
        PreOrderModel::findOrFail($id)->delete();
    }

    public function isUsedByOpenProductionPlan(int $id, ?int $exceptPlanId = null): bool
    {
        return ProductionPlanItem::where('pre_order_id', $id)
            ->whereHas('plan', fn ($q) => $q
                ->whereIn('status', [ProductionStatus::Planned->value, ProductionStatus::InProgress->value])
                ->when($exceptPlanId, fn ($q) => $q->where('id', '!=', $exceptPlanId)))
            ->exists();
    }
}
