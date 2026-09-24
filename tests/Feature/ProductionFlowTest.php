<?php

namespace Tests\Feature;

use App\Domain\PreOrder\PreOrderStatus;
use App\Domain\Production\ProductionStatus;
use App\Infrastructure\Persistence\Models\PreOrder;
use App\Infrastructure\Persistence\Models\ProductionPlan;
use App\Infrastructure\Persistence\Models\StockBatch;
use App\Infrastructure\Persistence\Models\StockMovement;
use App\Infrastructure\Persistence\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('email', 'user@example.com')->first());
    }

    public function test_produksi_selesai_memotong_stok_dan_menyelesaikan_pre_order(): void
    {
        $plan = ProductionPlan::where('status', ProductionStatus::Planned->value)->firstOrFail();
        $stockBefore = StockBatch::sum('quantity_remaining');

        $this->patch(route('production.start', $plan->id))->assertSessionHas('success');
        $this->patch(route('production.complete', $plan->id))->assertSessionHas('success');

        $this->assertSame(ProductionStatus::Completed, $plan->fresh()->status);
        $this->assertLessThan($stockBefore, StockBatch::sum('quantity_remaining'));
        $this->assertTrue(StockMovement::where('production_plan_id', $plan->id)->exists());
        $plan->items->pluck('pre_order_id')->filter()->unique()->each(
            fn ($id) => $this->assertSame(PreOrderStatus::Completed, PreOrder::find($id)->status)
        );
    }

    public function test_produksi_ditolak_bila_bahan_kurang_dan_stok_tidak_berubah(): void
    {
        $this->post(route('production.store'), [
            'plan_date' => today()->addDay()->toDateString(),
            'items' => [['product_id' => 3, 'quantity' => 500]],
        ]);
        $plan = ProductionPlan::latest('id')->first();
        $this->patch(route('production.start', $plan->id));
        $stockBefore = StockBatch::sum('quantity_remaining');

        $this->patch(route('production.complete', $plan->id))->assertSessionHas('error');

        $this->assertSame(ProductionStatus::InProgress, $plan->fresh()->status);
        $this->assertEquals($stockBefore, StockBatch::sum('quantity_remaining'));
    }

    public function test_pre_order_bisa_dibuat_lewat_form(): void
    {
        $this->post(route('pre-orders.store'), [
            'customer_name' => 'Bu Tes',
            'order_date' => today()->toDateString(),
            'due_date' => today()->addDays(2)->toDateString(),
            'down_payment' => 10000,
            'items' => [['product_id' => 1, 'quantity' => 5, 'unit_price' => 8000]],
        ])->assertRedirect();

        $order = PreOrder::where('customer_name', 'Bu Tes')->firstOrFail();
        $this->assertSame(PreOrderStatus::Draft, $order->status);
        $this->assertEquals(40000, $order->total);
    }
}
