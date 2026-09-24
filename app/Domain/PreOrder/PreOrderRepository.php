<?php

namespace App\Domain\PreOrder;

interface PreOrderRepository
{
    public function find(int $id): ?PreOrder;

    /** Menyimpan pre-order baru atau perubahan, mengembalikan ID-nya. */
    public function save(PreOrder $order, int $actorId): int;

    public function delete(int $id): void;

    /** Apakah pre-order sedang dipakai rencana produksi yang belum selesai/dibatalkan. */
    public function isUsedByOpenProductionPlan(int $id, ?int $exceptPlanId = null): bool;
}
