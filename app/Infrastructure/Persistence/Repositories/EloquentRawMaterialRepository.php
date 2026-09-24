<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Inventory\RawMaterialDetails;
use App\Domain\Inventory\RawMaterialRepository;
use App\Infrastructure\Persistence\Models\RawMaterial;
use App\Infrastructure\Persistence\Models\StockBatch;
use Illuminate\Support\Facades\DB;

final class EloquentRawMaterialRepository implements RawMaterialRepository
{
    public function info(int $id): ?array
    {
        $m = RawMaterial::find($id);

        return $m ? ['name' => $m->name, 'unit' => $m->unit] : null;
    }

    public function save(?int $id, RawMaterialDetails $details): int
    {
        $model = $id ? RawMaterial::findOrFail($id) : new RawMaterial();
        $model->fill([
            'code' => strtoupper(trim($details->code)),
            'name' => trim($details->name),
            'unit' => $details->unit,
            'minimum_stock' => $details->minimumStock,
            'notes' => $details->notes,
        ])->save();

        return $model->id;
    }

    public function delete(int $id): void
    {
        RawMaterial::findOrFail($id)->delete();
    }

    public function hasStock(int $id): bool
    {
        return StockBatch::where('raw_material_id', $id)->where('quantity_remaining', '>', 0)->exists();
    }

    public function isUsedInRecipe(int $id): bool
    {
        return DB::table('product_materials')
            ->join('products', 'products.id', '=', 'product_materials.product_id')
            ->whereNull('products.deleted_at')
            ->where('raw_material_id', $id)
            ->exists();
    }
}
