<?php

namespace App\Infrastructure\Persistence\Models;

use App\Domain\Production\ProductionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionPlan extends Model
{
    use SoftDeletes;

    protected $fillable = ['number', 'plan_date', 'status', 'notes', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['plan_date' => 'date', 'status' => ProductionStatus::class];
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductionPlanItem::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
