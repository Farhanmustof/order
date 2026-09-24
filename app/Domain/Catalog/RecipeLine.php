<?php

namespace App\Domain\Catalog;

use App\Domain\Shared\BusinessRuleException;

/** Satu baris resep: berapa banyak bahan untuk membuat 1 satuan produk. */
final class RecipeLine
{
    public function __construct(
        public readonly int $rawMaterialId,
        public readonly float $quantityPerUnit,
    ) {
        if ($quantityPerUnit <= 0) {
            throw new BusinessRuleException('Takaran bahan di resep harus lebih dari 0.');
        }
    }

    /** @param RecipeLine[] $lines */
    public static function ensureUnique(array $lines): void
    {
        $ids = array_map(fn (self $l) => $l->rawMaterialId, $lines);
        if (count($ids) !== count(array_unique($ids))) {
            throw new BusinessRuleException('Bahan yang sama tidak boleh muncul dua kali di resep.');
        }
    }
}
