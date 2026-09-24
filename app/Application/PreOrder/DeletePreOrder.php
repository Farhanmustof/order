<?php

namespace App\Application\PreOrder;

use App\Application\Contracts\AuditLogger;
use App\Application\Contracts\TransactionManager;
use App\Domain\PreOrder\PreOrderRepository;
use App\Domain\Shared\BusinessRuleException;
use App\Domain\Shared\NotFoundException;

final class DeletePreOrder
{
    public function __construct(
        private PreOrderRepository $orders,
        private TransactionManager $tx,
        private AuditLogger $audit,
    ) {
    }

    public function execute(int $id, int $actorId): void
    {
        $order = $this->orders->find($id) ?? throw NotFoundException::of('Pre-order');
        $order->ensureDeletable();

        if ($this->orders->isUsedByOpenProductionPlan($id)) {
            throw new BusinessRuleException('Pre-order ini masih dipakai rencana produksi yang berjalan.');
        }

        $this->tx->run(function () use ($order, $id, $actorId) {
            $this->orders->delete($id);
            $this->audit->log($actorId, 'delete', 'pre_order', $id, "Menghapus pre-order {$order->customerName()}");
        });
    }
}
