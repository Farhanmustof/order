<?php

namespace App\Domain\Inventory;

interface RawMaterialRepository
{
    /** @return array{name: string, unit: string}|null */
    public function info(int $id): ?array;

    public function save(?int $id, RawMaterialDetails $details): int;

    public function delete(int $id): void;

    public function hasStock(int $id): bool;

    public function isUsedInRecipe(int $id): bool;
}
