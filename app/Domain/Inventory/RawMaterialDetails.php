<?php

namespace App\Domain\Inventory;

use App\Domain\Shared\BusinessRuleException;

final class RawMaterialDetails
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly string $unit,
        public readonly float $minimumStock,
        public readonly ?string $notes,
    ) {
        if (trim($code) === '' || trim($name) === '') {
            throw new BusinessRuleException('Kode dan nama bahan baku wajib diisi.');
        }
        if ($minimumStock < 0) {
            throw new BusinessRuleException('Stok minimum tidak boleh negatif.');
        }
    }
}
