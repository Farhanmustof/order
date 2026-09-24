<?php

namespace App\Domain\Inventory;

use App\Domain\Shared\BusinessRuleException;

class InsufficientStockException extends BusinessRuleException
{
    public function __construct(string $materialName, float $needed, float $available, string $unit = '', bool $expiredExcluded = true)
    {
        $fmt = fn (float $n) => rtrim(rtrim(number_format($n, 3, ',', '.'), '0'), ',');
        parent::__construct(
            "Stok {$materialName} tidak cukup: butuh {$fmt($needed)} {$unit}, tersedia {$fmt($available)} {$unit}"
            .($expiredExcluded ? ' (batch kedaluwarsa tidak dihitung).' : '.')
        );
    }
}
