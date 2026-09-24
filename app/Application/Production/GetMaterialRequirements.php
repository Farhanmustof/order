<?php

namespace App\Application\Production;

use App\Application\Contracts\Clock;
use App\Domain\Catalog\ProductRepository;
use App\Domain\Inventory\RawMaterialRepository;
use App\Domain\Inventory\StockBatch;
use App\Domain\Inventory\StockRepository;
use App\Domain\Production\MaterialRequirementCalculator;

/**
 * Membandingkan kebutuhan bahan dengan stok yang bisa dipakai (belum kedaluwarsa).
 */
final class GetMaterialRequirements
{
    public function __construct(
        private ProductRepository $products,
        private RawMaterialRepository $materials,
        private StockRepository $stock,
        private MaterialRequirementCalculator $calculator,
        private Clock $clock,
    ) {
    }

    /**
     * @param array<int, float> $quantitiesByProduct
     * @return array{rows: array<int, array{material_id:int, name:string, unit:string, needed:float, available:float, shortage:float}>, missing_recipes: string[]}
     */
    public function execute(array $quantitiesByProduct): array
    {
        $bom = $this->products->billOfMaterials(array_keys($quantitiesByProduct));
        $today = $this->clock->today();
        $rows = [];

        foreach ($this->calculator->calculate($quantitiesByProduct, $bom) as $materialId => $needed) {
            $info = $this->materials->info($materialId) ?? ['name' => "Bahan #{$materialId}", 'unit' => ''];
            $available = round(array_sum(array_map(
                fn (StockBatch $b) => $b->isExpired($today) ? 0 : $b->quantityRemaining,
                $this->stock->batchesWithStock($materialId),
            )), 3);

            $rows[] = [
                'material_id' => $materialId,
                'name' => $info['name'],
                'unit' => $info['unit'],
                'needed' => $needed,
                'available' => $available,
                'shortage' => max(0, round($needed - $available, 3)),
            ];
        }

        usort($rows, fn ($a, $b) => [$b['shortage'] > 0, $a['name']] <=> [$a['shortage'] > 0, $b['name']]);

        return [
            'rows' => $rows,
            'missing_recipes' => array_values($this->products->names(
                $this->calculator->productsWithoutRecipe($quantitiesByProduct, $bom)
            )),
        ];
    }
}
