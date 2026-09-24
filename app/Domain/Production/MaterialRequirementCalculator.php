<?php

namespace App\Domain\Production;

/**
 * Menghitung kebutuhan bahan baku dari jumlah produk dan resep (BOM).
 *
 * $bom berbentuk [productId => [rawMaterialId => jumlahPerSatuanProduk]].
 */
final class MaterialRequirementCalculator
{
    /**
     * @param array<int, float> $quantitiesByProduct [productId => jumlah]
     * @param array<int, array<int, float>> $bom
     * @return array<int, float> [rawMaterialId => total kebutuhan]
     */
    public function calculate(array $quantitiesByProduct, array $bom): array
    {
        $needs = [];
        foreach ($quantitiesByProduct as $productId => $quantity) {
            foreach ($bom[$productId] ?? [] as $materialId => $perUnit) {
                $needs[$materialId] = round(($needs[$materialId] ?? 0) + $perUnit * $quantity, 3);
            }
        }
        ksort($needs);

        return $needs;
    }

    /** @return int[] produk yang belum punya resep */
    public function productsWithoutRecipe(array $quantitiesByProduct, array $bom): array
    {
        return array_values(array_filter(
            array_keys($quantitiesByProduct),
            fn (int $productId) => empty($bom[$productId]),
        ));
    }
}
