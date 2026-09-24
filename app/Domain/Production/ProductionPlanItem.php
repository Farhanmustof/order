<?php

namespace App\Domain\Production;

use App\Domain\Shared\BusinessRuleException;

final class ProductionPlanItem
{
    public function __construct(
        public readonly int $productId,
        public readonly float $quantity,
        public readonly ?int $preOrderId = null,
    ) {
        if ($quantity <= 0) {
            throw new BusinessRuleException('Jumlah produksi harus lebih dari 0.');
        }
    }
}
