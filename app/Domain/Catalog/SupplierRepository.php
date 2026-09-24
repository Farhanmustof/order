<?php

namespace App\Domain\Catalog;

interface SupplierRepository
{
    public function exists(int $id): bool;

    public function save(?int $id, SupplierDetails $details): int;

    public function delete(int $id): void;
}
