<?php

namespace App\Domain\Production;

interface ProductionPlanRepository
{
    public function find(int $id): ?ProductionPlan;

    public function save(ProductionPlan $plan, int $actorId): int;

    public function delete(int $id): void;
}
