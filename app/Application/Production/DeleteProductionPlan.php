<?php

namespace App\Application\Production;

use App\Application\Contracts\AuditLogger;
use App\Application\Contracts\TransactionManager;
use App\Domain\Production\ProductionPlanRepository;
use App\Domain\Shared\NotFoundException;

final class DeleteProductionPlan
{
    public function __construct(
        private ProductionPlanRepository $plans,
        private TransactionManager $tx,
        private AuditLogger $audit,
    ) {
    }

    public function execute(int $id, int $actorId): void
    {
        $plan = $this->plans->find($id) ?? throw NotFoundException::of('Rencana produksi');
        $plan->ensureDeletable();

        $this->tx->run(function () use ($id, $actorId) {
            $this->plans->delete($id);
            $this->audit->log($actorId, 'delete', 'production_plan', $id, 'Menghapus rencana produksi');
        });
    }
}
