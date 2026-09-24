<?php

namespace App\Infrastructure\Queries;

use App\Domain\PreOrder\PreOrderStatus;
use App\Infrastructure\Persistence\Models\PreOrder;
use App\Infrastructure\Persistence\Models\ProductionPlan;

final class DashboardQuery
{
    public function __construct(private InventoryQuery $inventory)
    {
    }

    public function summary(): array
    {
        $open = [PreOrderStatus::Draft->value, PreOrderStatus::Confirmed->value, PreOrderStatus::InProduction->value, PreOrderStatus::Completed->value];
        $weekEnd = today()->addDays(7)->toDateString();

        $upcoming = PreOrder::whereIn('status', $open)
            ->where('due_date', '<=', $weekEnd)
            ->orderBy('due_date')
            ->limit(8)
            ->get();

        $materials = $this->inventory->materialsWithStock();

        return [
            'open_orders' => PreOrder::whereIn('status', $open)->count(),
            'overdue_orders' => PreOrder::whereIn('status', $open)->where('due_date', '<', today()->toDateString())->count(),
            'month_revenue' => (float) PreOrder::whereNotIn('status', [PreOrderStatus::Cancelled->value])
                ->whereBetween('order_date', [today()->startOfMonth()->toDateString(), today()->endOfMonth()->toDateString()])
                ->sum('total'),
            'upcoming_orders' => $upcoming,
            'status_counts' => PreOrder::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'plans' => ProductionPlan::with('items.product')
                ->whereIn('status', ['planned', 'in_progress'])
                ->orderBy('plan_date')
                ->limit(6)
                ->get(),
            'materials' => $materials,
            'low_stock' => $materials->where('is_low', true)->values(),
            'expiring' => $this->inventory->expiringBatches(),
        ];
    }
}
