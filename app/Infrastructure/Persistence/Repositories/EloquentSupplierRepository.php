<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Catalog\SupplierDetails;
use App\Domain\Catalog\SupplierRepository;
use App\Infrastructure\Persistence\Models\Supplier;

final class EloquentSupplierRepository implements SupplierRepository
{
    public function exists(int $id): bool
    {
        return Supplier::whereKey($id)->exists();
    }

    public function save(?int $id, SupplierDetails $details): int
    {
        $model = $id ? Supplier::findOrFail($id) : new Supplier();
        $model->fill([
            'name' => trim($details->name),
            'phone' => $details->phone,
            'address' => $details->address,
            'notes' => $details->notes,
        ])->save();

        return $model->id;
    }

    public function delete(int $id): void
    {
        Supplier::findOrFail($id)->delete();
    }
}
