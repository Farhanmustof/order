<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockBatch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'raw_material_id', 'supplier_id', 'batch_code', 'received_date', 'expiry_date',
        'quantity_initial', 'quantity_remaining', 'unit_cost', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'received_date' => 'date',
            'expiry_date' => 'date',
            'quantity_initial' => 'float',
            'quantity_remaining' => 'float',
            'unit_cost' => 'float',
        ];
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_id')->withTrashed();
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function isExpired(): bool
    {
        return $this->expiry_date !== null && $this->expiry_date->lt(today());
    }

    public function daysToExpiry(): ?int
    {
        return $this->expiry_date ? (int) today()->diffInDays($this->expiry_date, false) : null;
    }

    public function isUntouched(): bool
    {
        return abs($this->quantity_initial - $this->quantity_remaining) < 0.0005;
    }
}
