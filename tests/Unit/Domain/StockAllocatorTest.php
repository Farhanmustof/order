<?php

namespace Tests\Unit\Domain;

use App\Domain\Inventory\InsufficientStockException;
use App\Domain\Inventory\StockAllocator;
use App\Domain\Inventory\StockBatch;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class StockAllocatorTest extends TestCase
{
    private function batch(int $id, string $received, ?string $expiry, float $qty): StockBatch
    {
        return new StockBatch($id, 1, new DateTimeImmutable($received), $expiry ? new DateTimeImmutable($expiry) : null, $qty, $qty);
    }

    public function test_kedaluwarsa_terdekat_dipakai_lebih_dulu(): void
    {
        $batches = [
            $this->batch(1, '2026-09-01', '2026-12-01', 10),
            $this->batch(2, '2026-09-10', '2026-10-01', 5),
        ];

        $result = (new StockAllocator())->allocate($batches, 8, new DateTimeImmutable('2026-09-24'));

        $this->assertSame([2, 1], array_map(fn ($a) => $a->batchId, $result));
        $this->assertSame([5.0, 3.0], array_map(fn ($a) => $a->quantity, $result));
    }

    public function test_batch_kedaluwarsa_tidak_dipakai_produksi(): void
    {
        $batches = [
            $this->batch(1, '2026-08-01', '2026-09-20', 4),
            $this->batch(2, '2026-09-10', '2026-10-01', 5),
        ];

        $result = (new StockAllocator())->allocate($batches, 3, new DateTimeImmutable('2026-09-24'));

        $this->assertCount(1, $result);
        $this->assertSame(2, $result[0]->batchId);
    }

    public function test_stok_kedaluwarsa_boleh_dikeluarkan_manual(): void
    {
        $batches = [$this->batch(1, '2026-08-01', '2026-09-20', 4)];

        $result = (new StockAllocator())->allocate($batches, 4, new DateTimeImmutable('2026-09-24'), includeExpired: true);

        $this->assertSame(1, $result[0]->batchId);
    }

    public function test_menolak_bila_stok_kurang(): void
    {
        $this->expectException(InsufficientStockException::class);
        $this->expectExceptionMessage('Stok Tepung tidak cukup');

        (new StockAllocator())->allocate([$this->batch(1, '2026-09-01', null, 2)], 5, new DateTimeImmutable('2026-09-24'), 'Tepung', 'kg');
    }
}
