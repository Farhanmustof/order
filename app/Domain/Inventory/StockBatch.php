<?php

namespace App\Domain\Inventory;

use DateTimeImmutable;

/** Satu kali penerimaan bahan baku (satu batch), dilacak sampai habis. */
final class StockBatch
{
    public function __construct(
        public readonly int $id,
        public readonly int $rawMaterialId,
        public readonly DateTimeImmutable $receivedDate,
        public readonly ?DateTimeImmutable $expiryDate,
        public readonly float $quantityInitial,
        public readonly float $quantityRemaining,
    ) {
    }

    public function isExpired(DateTimeImmutable $today): bool
    {
        return $this->expiryDate !== null && $this->expiryDate < $today->setTime(0, 0);
    }

    public function isUntouched(): bool
    {
        return abs($this->quantityInitial - $this->quantityRemaining) < 0.0005;
    }
}
