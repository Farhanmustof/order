<?php

namespace Tests\Unit\Domain;

use App\Domain\PreOrder\PreOrder;
use App\Domain\PreOrder\PreOrderItem;
use App\Domain\PreOrder\PreOrderStatus;
use App\Domain\Shared\BusinessRuleException;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class PreOrderTest extends TestCase
{
    private function order(float $dp = 0, array $items = null): PreOrder
    {
        return PreOrder::create(
            'Bu Sari', null, null,
            new DateTimeImmutable('2026-09-24'), new DateTimeImmutable('2026-09-30'),
            $dp, null, $items ?? [new PreOrderItem(1, 10, 25000)],
        );
    }

    public function test_menghitung_total_dan_sisa_bayar(): void
    {
        $order = $this->order(50000, [new PreOrderItem(1, 10, 25000), new PreOrderItem(2, 2, 85000)]);

        $this->assertSame(420000.0, $order->total());
        $this->assertSame(370000.0, $order->remainingPayment());
        $this->assertSame(PreOrderStatus::Draft, $order->status());
    }

    public function test_dp_tidak_boleh_melebihi_total(): void
    {
        $this->expectException(BusinessRuleException::class);
        $this->order(300000);
    }

    public function test_jatuh_tempo_tidak_boleh_sebelum_tanggal_pesan(): void
    {
        $this->expectException(BusinessRuleException::class);
        PreOrder::create('X', null, null, new DateTimeImmutable('2026-09-24'), new DateTimeImmutable('2026-09-20'), 0, null, [new PreOrderItem(1, 1, 1)]);
    }

    public function test_status_harus_mengikuti_alur(): void
    {
        $order = $this->order();
        $order->moveTo(PreOrderStatus::Confirmed);
        $order->moveTo(PreOrderStatus::InProduction);

        $this->expectException(BusinessRuleException::class);
        $order->moveTo(PreOrderStatus::Delivered);
    }

    public function test_tidak_bisa_diubah_setelah_diproduksi(): void
    {
        $order = $this->order();
        $order->moveTo(PreOrderStatus::Confirmed);
        $order->moveTo(PreOrderStatus::InProduction);

        $this->expectException(BusinessRuleException::class);
        $order->update('Bu Sari', null, null, new DateTimeImmutable('2026-09-24'), new DateTimeImmutable('2026-09-30'), 0, null, [new PreOrderItem(1, 5, 25000)]);
    }
}
