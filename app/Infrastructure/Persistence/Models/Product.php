<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'name', 'unit', 'price', 'description'];

    protected function casts(): array
    {
        return ['price' => 'float'];
    }

    /** Resep: bahan baku dan takarannya per 1 satuan produk. */
    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(RawMaterial::class, 'product_materials')
            ->withPivot('quantity')
            ->withTrashed();
    }
}
