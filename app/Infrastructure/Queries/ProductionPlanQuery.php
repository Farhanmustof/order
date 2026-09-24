<?php

namespace App\Infrastructure\Queries;

use App\Infrastructure\Persistence\Models\ProductionPlan;

final class ProductionPlanQuery
{
    public function paginate(array $filters, int $perPage = 20)
    {
        return ProductionPlan::with(['items.product'])
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['from'] ?? null, fn ($q, $v) => $q->whereDate('plan_date', '>=', $v))
            ->when($filters['to'] ?? null, fn ($q, $v) => $q->whereDate('plan_date', '<=', $v))
            ->orderByRaw("case when status in ('completed','cancelled') then 1 else 0 end")
            ->orderBy('plan_date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function detail(int $id): ProductionPlan
    {
        return ProductionPlan::with(['items.product', 'items.preOrder', 'creator', 'movements.material', 'movements.batch'])->findOrFail($id);
    }

    /** Ringkasan jumlah per produk untuk ditampilkan. */
    public function productTotals(ProductionPlan $plan)
    {
        return $plan->items->groupBy('product_id')->map(fn ($rows) => (object) [
            'product' => $rows->first()->product,
            'quantity' => round($rows->sum('quantity'), 3),
            'from_orders' => round($rows->whereNotNull('pre_order_id')->sum('quantity'), 3),
            'extra' => round($rows->whereNull('pre_order_id')->sum('quantity'), 3),
        ])->values();
    }
}
