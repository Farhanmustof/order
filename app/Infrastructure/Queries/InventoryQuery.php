<?php

namespace App\Infrastructure\Queries;

use App\Infrastructure\Persistence\Models\RawMaterial;
use App\Infrastructure\Persistence\Models\StockBatch;
use App\Infrastructure\Persistence\Models\StockMovement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/** Query baca untuk halaman stok (sisi "read" dari clean architecture). */
final class InventoryQuery
{
    public const EXPIRY_WARNING_DAYS = 14;

    public function materialsWithStock(?string $search = null, ?string $filter = null): Collection
    {
        $today = today()->toDateString();

        $materials = RawMaterial::query()
            ->withSum(['batches as stock' => fn (Builder $q) => $q->where('quantity_remaining', '>', 0)], 'quantity_remaining')
            ->withSum(['batches as expired_stock' => fn (Builder $q) => $q
                ->where('quantity_remaining', '>', 0)
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '<', $today)], 'quantity_remaining')
            ->withMin(['batches as next_expiry' => fn (Builder $q) => $q
                ->where('quantity_remaining', '>', 0)
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '>=', $today)], 'expiry_date')
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")))
            ->orderBy('name')
            ->get()
            ->each(function (RawMaterial $m) {
                $m->stock = round((float) $m->stock, 3);
                $m->expired_stock = round((float) $m->expired_stock, 3);
                $m->usable_stock = round($m->stock - $m->expired_stock, 3);
                $m->is_low = $m->usable_stock <= $m->minimum_stock;
                $m->days_to_expiry = $m->next_expiry ? (int) today()->diffInDays($m->next_expiry, false) : null;
            });

        return match ($filter) {
            'low' => $materials->where('is_low', true)->values(),
            'expiring' => $materials->filter(fn ($m) => $m->expired_stock > 0
                || ($m->days_to_expiry !== null && $m->days_to_expiry <= self::EXPIRY_WARNING_DAYS))->values(),
            default => $materials,
        };
    }

    public function lowStock(): Collection
    {
        return $this->materialsWithStock(filter: 'low');
    }

    /** Batch yang sudah atau akan kedaluwarsa dalam N hari dan masih ada sisanya. */
    public function expiringBatches(int $days = self::EXPIRY_WARNING_DAYS): Collection
    {
        return StockBatch::with('material')
            ->where('quantity_remaining', '>', 0)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', today()->addDays($days)->toDateString())
            ->orderBy('expiry_date')
            ->get();
    }

    public function batchesOf(int $materialId, bool $onlyWithStock = false)
    {
        return StockBatch::with('supplier')
            ->where('raw_material_id', $materialId)
            ->when($onlyWithStock, fn ($q) => $q->where('quantity_remaining', '>', 0))
            ->orderByRaw('quantity_remaining > 0 desc')
            ->orderByRaw('expiry_date is null')
            ->orderBy('expiry_date')
            ->orderBy('received_date')
            ->get();
    }

    public function movements(array $filters = [], int $perPage = 25)
    {
        return $this->movementQuery($filters)->paginate($perPage)->withQueryString();
    }

    public function movementQuery(array $filters = []): Builder
    {
        return StockMovement::with(['material', 'batch', 'plan', 'creator'])
            // sembunyikan pergerakan milik batch yang dihapus
            ->where(fn ($q) => $q->whereNull('stock_batch_id')->orWhereHas('batch'))
            ->when($filters['material_id'] ?? null, fn ($q, $v) => $q->where('raw_material_id', $v))
            ->when($filters['type'] ?? null, fn ($q, $v) => $q->where('type', $v))
            ->when($filters['from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest('id');
    }
}
