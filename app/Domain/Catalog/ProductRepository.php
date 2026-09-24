<?php

namespace App\Domain\Catalog;

interface ProductRepository
{
    public function exists(int $id): bool;

    /** @param RecipeLine[] $recipe */
    public function save(?int $id, ProductDetails $details, array $recipe): int;

    public function delete(int $id): void;

    /**
     * Resep untuk beberapa produk sekaligus.
     *
     * @param int[] $productIds
     * @return array<int, array<int, float>> [productId => [rawMaterialId => jumlahPerSatuan]]
     */
    public function billOfMaterials(array $productIds): array;

    /** @return array<int, string> [productId => nama] */
    public function names(array $productIds): array;

    /** Dipakai pre-order aktif atau rencana produksi yang masih berjalan. */
    public function isInUse(int $id): bool;
}
