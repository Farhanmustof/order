<?php

namespace App\Domain\Inventory;

final class StockAllocation
{
    public function __construct(
        public readonly int $batchId,
        public readonly float $quantity,
    ) {
    }
}
