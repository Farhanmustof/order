<?php

namespace App\Domain\PreOrder;

use App\Domain\Shared\BusinessRuleException;

final class PreOrderItem
{
    public function __construct(
        public readonly int $productId,
        public readonly float $quantity,
        public readonly float $unitPrice,
    ) {
        if ($quantity <= 0) {
            throw new BusinessRuleException('Jumlah produk harus lebih dari 0.');
        }
        if ($unitPrice < 0) {
            throw new BusinessRuleException('Harga satuan tidak boleh negatif.');
        }
    }

    public function subtotal(): float
    {
        return round($this->quantity * $this->unitPrice, 2);
    }
}
