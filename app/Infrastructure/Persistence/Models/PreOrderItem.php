<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreOrderItem extends Model
{
    public $timestamps = false;

    protected $fillable = ['pre_order_id', 'product_id', 'quantity', 'unit_price'];

    protected function casts(): array
    {
        return ['quantity' => 'float', 'unit_price' => 'float'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function preOrder(): BelongsTo
    {
        return $this->belongsTo(PreOrder::class);
    }
}
