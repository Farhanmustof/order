<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    public $timestamps = false;

    protected $fillable = ['purchase_order_id', 'raw_material_id', 'quantity', 'unit_cost'];

    protected function casts(): array
    {
        return ['quantity' => 'float', 'unit_cost' => 'float'];
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_id')->withTrashed();
    }
}
