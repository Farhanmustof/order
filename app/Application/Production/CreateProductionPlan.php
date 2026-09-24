<?php

namespace App\Application\Production;

use App\Application\Contracts\AuditLogger;
use App\Application\Contracts\Dates;
use App\Application\Contracts\TransactionManager;
use App\Domain\Production\ProductionPlan;
use App\Domain\Production\ProductionPlanRepository;

final class CreateProductionPlan
{
    public function __construct(
        private ProductionPlanRepository $plans,
        private PlanItemsBuilder $builder,
        private TransactionManager $tx,
        private AuditLogger $audit,
    ) {
    }

    public function execute(ProductionPlanInput $input, int $actorId): int
    {
        return $this->tx->run(function () use ($input, $actorId) {
            $plan = ProductionPlan::create(
                Dates::required($input->planDate, 'Tanggal produksi'),
                $input->notes,
                $this->builder->build($input),
            );
            $id = $this->plans->save($plan, $actorId);
            $this->audit->log($actorId, 'create', 'production_plan', $id, 'Membuat rencana produksi tanggal '.$plan->planDate()->format('d/m/Y'));

            return $id;
        });
    }
}
