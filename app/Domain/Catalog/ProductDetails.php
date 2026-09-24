<?php

namespace App\Domain\Catalog;

use App\Domain\Shared\BusinessRuleException;

final class ProductDetails
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly string $unit,
        public readonly float $price,
        public readonly ?string $description,
    ) {
        if (trim($code) === '' || trim($name) === '') {
            throw new BusinessRuleException('Kode dan nama produk wajib diisi.');
        }
        if ($price < 0) {
            throw new BusinessRuleException('Harga produk tidak boleh negatif.');
        }
    }
}
