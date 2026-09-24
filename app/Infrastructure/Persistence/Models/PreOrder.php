<?php

namespace App\Infrastructure\Persistence\Models;

use App\Domain\PreOrder\PreOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'number', 'customer_name', 'customer_phone', 'customer_address', 'order_date', 'due_date',
        'status', 'down_payment', 'total', 'notes', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'due_date' => 'date',
            'status' => PreOrderStatus::class,
            'down_payment' => 'float',
            'total' => 'float',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(PreOrderItem::class);
    }

    public function planItems(): HasMany
    {
        return $this->hasMany(ProductionPlanItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
