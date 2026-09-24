<?php

namespace App\Application\Production;

use App\Application\Contracts\AuditLogger;
use App\Application\Contracts\TransactionManager;
use App\Domain\PreOrder\PreOrderRepository;
use App\Domain\PreOrder\PreOrderStatus;
use App\Domain\Production\ProductionPlanRepository;
use App\Domain\Shared\NotFoundException;

final class CancelProduction
{
    public function __construct(
        private ProductionPlanRepository $plans,
        private PreOrderRepository $orders,
        private TransactionManager $tx,
        private AuditLogger $audit,
    ) {
    }

    public function execute(int $id, int $actorId): void
    {
        $this->tx->run(function () use ($id, $actorId) {
            $plan = $this->plans->find($id) ?? throw NotFoundException::of('Rencana produksi');
            $plan->cancel();
            $this->plans->save($plan, $actorId);

            // Pre-order yang tadinya ikut diproduksi kembali ke Dikonfirmasi agar bisa dijadwalkan ulang.
            foreach ($plan->preOrderIds() as $preOrderId) {
                $order = $this->orders->find($preOrderId);
                if ($order && $order->status() === PreOrderStatus::InProduction) {
                    $order->returnToConfirmed();
                    $this->orders->save($order, $actorId);
                }
            }

            $this->audit->log($actorId, 'status', 'production_plan', $id, 'Membatalkan rencana produksi');
        });
    }
}
