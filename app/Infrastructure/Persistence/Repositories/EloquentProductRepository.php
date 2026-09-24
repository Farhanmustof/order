<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Catalog\ProductDetails;
use App\Domain\Catalog\ProductRepository;
use App\Domain\Catalog\RecipeLine;
use App\Domain\PreOrder\PreOrderStatus;
use App\Domain\Production\ProductionStatus;
use App\Infrastructure\Persistence\Models\PreOrderItem;
use App\Infrastructure\Persistence\Models\Product;
use App\Infrastructure\Persistence\Models\ProductionPlanItem;
use Illuminate\Support\Facades\DB;

final class EloquentProductRepository implements ProductRepository
{
    public function exists(int $id): bool
    {
        return Product::whereKey($id)->exists();
    }

    public function save(?int $id, ProductDetails $details, array $recipe): int
    {
        $model = $id ? Product::findOrFail($id) : new Product();
        $model->fill([
            'code' => strtoupper(trim($details->code)),
            'name' => trim($details->name),
            'unit' => $details->unit,
            'price' => $details->price,
            'description' => $details->description,
        ])->save();

        $model->materials()->sync(collect($recipe)->mapWithKeys(
            fn (RecipeLine $line) => [$line->rawMaterialId => ['quantity' => $line->quantityPerUnit]]
        )->all());

        return $model->id;
    }

    public function delete(int $id): void
    {
        Product::findOrFail($id)->delete();
    }

    public function billOfMaterials(array $productIds): array
    {
        $bom = [];
        DB::table('product_materials')
            ->whereIn('product_id', $productIds)
            ->get(['product_id', 'raw_material_id', 'quantity'])
            ->each(function ($row) use (&$bom) {
                $bom[(int) $row->product_id][(int) $row->raw_material_id] = (float) $row->quantity;
            });

        return $bom;
    }

    public function names(array $productIds): array
    {
        return Product::withTrashed()->whereIn('id', $productIds)->pluck('name', 'id')->all();
    }

    public function isInUse(int $id): bool
    {
        $activeStatuses = array_map(fn ($s) => $s->value, PreOrderStatus::active());

        return PreOrderItem::where('product_id', $id)
                ->whereHas('preOrder', fn ($q) => $q->whereIn('status', $activeStatuses))
                ->exists()
            || ProductionPlanItem::where('product_id', $id)
                ->whereHas('plan', fn ($q) => $q->whereIn('status', [ProductionStatus::Planned->value, ProductionStatus::InProgress->value]))
                ->exists();
    }
}
