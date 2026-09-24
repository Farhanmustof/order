<?php

namespace App\Infrastructure\Queries;

use App\Domain\PreOrder\PreOrderStatus;
use App\Infrastructure\Persistence\Models\PreOrder;
use Illuminate\Database\Eloquent\Builder;

final class PreOrderQuery
{
    public function paginate(array $filters, int $perPage = 20)
    {
        return $this->query($filters)->paginate($perPage)->withQueryString();
    }

    public function query(array $filters = []): Builder
    {
        return PreOrder::with('items.product')
            ->when($filters['q'] ?? null, fn ($q, $v) => $q->where(fn ($q) => $q
                ->where('number', 'like', "%{$v}%")
                ->orWhere('customer_name', 'like', "%{$v}%")
                ->orWhere('customer_phone', 'like', "%{$v}%")))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['from'] ?? null, fn ($q, $v) => $q->whereDate('due_date', '>=', $v))
            ->when($filters['to'] ?? null, fn ($q, $v) => $q->whereDate('due_date', '<=', $v))
            ->orderByRaw("case when status in ('delivered','cancelled') then 1 else 0 end")
            ->orderBy('due_date')
            ->orderByDesc('id');
    }

    public function detail(int $id): PreOrder
    {
        return PreOrder::with(['items.product', 'creator', 'editor', 'planItems.plan'])->findOrFail($id);
    }

    /** Pre-order Dikonfirmasi yang belum masuk rencana produksi lain (untuk form rencana). */
    public function readyForProduction(?int $exceptPlanId = null)
    {
        return PreOrder::with('items.product')
            ->where('status', PreOrderStatus::Confirmed->value)
            ->whereDoesntHave('planItems.plan', fn ($q) => $q
                ->whereIn('status', ['planned', 'in_progress'])
                ->when($exceptPlanId, fn ($q) => $q->where('id', '!=', $exceptPlanId)))
            ->orderBy('due_date')
            ->get();
    }
}
