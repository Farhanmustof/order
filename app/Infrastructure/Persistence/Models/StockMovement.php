<?php

namespace App\Infrastructure\Persistence\Models;

use App\Domain\Inventory\StockMovementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = ['raw_material_id', 'stock_batch_id', 'production_plan_id', 'type', 'quantity', 'notes', 'created_by'];

    protected function casts(): array
    {
        return ['type' => StockMovementType::class, 'quantity' => 'float'];
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_id')->withTrashed();
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(StockBatch::class, 'stock_batch_id')->withTrashed();
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(ProductionPlan::class, 'production_plan_id')->withTrashed();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
