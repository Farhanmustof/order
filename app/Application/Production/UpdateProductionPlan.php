<?php

namespace App\Application\Production;

use App\Application\Contracts\AuditLogger;
use App\Application\Contracts\Dates;
use App\Application\Contracts\TransactionManager;
use App\Domain\Production\ProductionPlanRepository;
use App\Domain\Shared\NotFoundException;

final class UpdateProductionPlan
{
    public function __construct(
        private ProductionPlanRepository $plans,
        private PlanItemsBuilder $builder,
        private TransactionManager $tx,
        private AuditLogger $audit,
    ) {
    }

    public function execute(int $id, ProductionPlanInput $input, int $actorId): void
    {
        $this->tx->run(function () use ($id, $input, $actorId) {
            $plan = $this->plans->find($id) ?? throw NotFoundException::of('Rencana produksi');
            $plan->update(
                Dates::required($input->planDate, 'Tanggal produksi'),
                $input->notes,
                $this->builder->build($input, $id),
            );
            $this->plans->save($plan, $actorId);
            $this->audit->log($actorId, 'update', 'production_plan', $id, 'Mengubah rencana produksi');
        });
    }
}
