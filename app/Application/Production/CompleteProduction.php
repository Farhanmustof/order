<?php

namespace App\Application\Production;

use App\Application\Contracts\AuditLogger;
use App\Application\Contracts\Clock;
use App\Application\Contracts\TransactionManager;
use App\Domain\Catalog\ProductRepository;
use App\Domain\Inventory\RawMaterialRepository;
use App\Domain\Inventory\StockAllocator;
use App\Domain\Inventory\StockMovementType;
use App\Domain\Inventory\StockRepository;
use App\Domain\PreOrder\PreOrderRepository;
use App\Domain\PreOrder\PreOrderStatus;
use App\Domain\Production\MaterialRequirementCalculator;
use App\Domain\Production\ProductionPlanRepository;
use App\Domain\Shared\BusinessRuleException;
use App\Domain\Shared\NotFoundException;

/**
 * Menandai produksi selesai:
 * 1. menghitung kebutuhan bahan dari resep,
 * 2. mengurangi stok per batch dengan aturan FEFO,
 * 3. menandai pre-order terkait sebagai Selesai.
 * Semua dalam satu transaksi: bila satu bahan kurang, tidak ada yang berubah.
 */
final class CompleteProduction
{
    public function __construct(
        private ProductionPlanRepository $plans,
        private PreOrderRepository $orders,
        private ProductRepository $products,
        private RawMaterialRepository $materials,
        private StockRepository $stock,
        private MaterialRequirementCalculator $calculator,
        private StockAllocator $allocator,
        private Clock $clock,
        private TransactionManager $tx,
        private AuditLogger $audit,
    ) {
    }

    public function execute(int $id, int $actorId): void
    {
        $this->tx->run(function () use ($id, $actorId) {
            $plan = $this->plans->find($id) ?? throw NotFoundException::of('Rencana produksi');
            $plan->complete();

            $quantities = $plan->quantitiesByProduct();
            $bom = $this->products->billOfMaterials(array_keys($quantities));

            $missing = $this->calculator->productsWithoutRecipe($quantities, $bom);
            if ($missing !== []) {
                $names = implode(', ', $this->products->names($missing));
                throw new BusinessRuleException("Produk berikut belum punya resep, jadi pemakaian bahan tidak bisa dihitung: {$names}.");
            }

            $today = $this->clock->today();
            foreach ($this->calculator->calculate($quantities, $bom) as $materialId => $needed) {
                $info = $this->materials->info($materialId) ?? ['name' => "bahan #{$materialId}", 'unit' => ''];
                $allocations = $this->allocator->allocate(
                    $this->stock->batchesWithStock($materialId),
                    $needed,
                    $today,
                    $info['name'],
                    $info['unit'],
                );
                $this->stock->withdraw($materialId, $allocations, StockMovementType::Production, $id, null, $actorId);
            }

            $this->plans->save($plan, $actorId);

            foreach ($plan->preOrderIds() as $preOrderId) {
                $order = $this->orders->find($preOrderId);
                if ($order && $order->status() === PreOrderStatus::InProduction) {
                    $order->moveTo(PreOrderStatus::Completed);
                    $this->orders->save($order, $actorId);
                }
            }

            $this->audit->log($actorId, 'status', 'production_plan', $id, 'Menyelesaikan produksi dan memotong stok bahan baku');
        });
    }
}
