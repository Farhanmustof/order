<?php

namespace App\Domain\Inventory;

use DateTimeImmutable;

/**
 * Menentukan batch mana yang dipakai saat stok keluar, dengan aturan FEFO
 * (First Expired, First Out): kedaluwarsa terdekat dipakai lebih dulu,
 * lalu yang paling lama masuk. Cocok untuk bahan makanan.
 * Batch yang sudah kedaluwarsa tidak dipakai untuk produksi.
 */
final class StockAllocator
{
    /**
     * @param StockBatch[] $batches
     * @return StockAllocation[]
     */
    public function allocate(
        array $batches,
        float $quantity,
        DateTimeImmutable $today,
        string $materialName = 'bahan',
        string $unit = '',
        bool $includeExpired = false,
    ): array {
        $usable = array_filter(
            $batches,
            fn (StockBatch $b) => $b->quantityRemaining > 0 && ($includeExpired || ! $b->isExpired($today)),
        );

        usort($usable, function (StockBatch $a, StockBatch $b) {
            $ae = $a->expiryDate?->getTimestamp() ?? PHP_INT_MAX;
            $be = $b->expiryDate?->getTimestamp() ?? PHP_INT_MAX;

            return [$ae, $a->receivedDate->getTimestamp(), $a->id] <=> [$be, $b->receivedDate->getTimestamp(), $b->id];
        });

        $available = round(array_sum(array_map(fn (StockBatch $b) => $b->quantityRemaining, $usable)), 3);
        if ($available + 0.0005 < $quantity) {
            throw new InsufficientStockException($materialName, $quantity, $available, $unit, ! $includeExpired);
        }

        $allocations = [];
        $left = round($quantity, 3);
        foreach ($usable as $batch) {
            if ($left <= 0) {
                break;
            }
            $take = round(min($batch->quantityRemaining, $left), 3);
            $allocations[] = new StockAllocation($batch->id, $take);
            $left = round($left - $take, 3);
        }

        return $allocations;
    }
}
