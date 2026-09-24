<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Production\ProductionPlan;
use App\Domain\Production\ProductionPlanItem;
use App\Domain\Production\ProductionPlanRepository;
use App\Infrastructure\Persistence\Models\ProductionPlan as PlanModel;
use DateTimeImmutable;

final class EloquentProductionPlanRepository implements ProductionPlanRepository
{
    public function find(int $id): ?ProductionPlan
    {
        $model = PlanModel::with('items')->find($id);
        if ($model === null) {
            return null;
        }

        return ProductionPlan::restore(
            $model->id,
            DateTimeImmutable::createFromInterface($model->plan_date),
            $model->status,
            $model->notes,
            $model->items->map(fn ($i) => new ProductionPlanItem($i->product_id, $i->quantity, $i->pre_order_id))->all(),
        );
    }

    public function save(ProductionPlan $plan, int $actorId): int
    {
        $model = $plan->id ? PlanModel::findOrFail($plan->id) : new PlanModel([
            'number' => DocumentNumber::next(PlanModel::class, 'PP'),
            'created_by' => $actorId,
        ]);

        $model->fill([
            'plan_date' => $plan->planDate()->format('Y-m-d'),
            'status' => $plan->status(),
            'notes' => $plan->notes(),
            'updated_by' => $actorId,
        ])->save();

        $model->items()->delete();
        $model->items()->createMany(array_map(fn (ProductionPlanItem $i) => [
            'product_id' => $i->productId,
            'pre_order_id' => $i->preOrderId,
            'quantity' => $i->quantity,
        ], $plan->items()));

        return $model->id;
    }

    public function delete(int $id): void
    {
        PlanModel::findOrFail($id)->delete();
    }
}
