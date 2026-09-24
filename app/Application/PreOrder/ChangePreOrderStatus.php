<?php

namespace App\Application\PreOrder;

use App\Application\Contracts\AuditLogger;
use App\Application\Contracts\TransactionManager;
use App\Domain\PreOrder\PreOrderRepository;
use App\Domain\PreOrder\PreOrderStatus;
use App\Domain\Shared\BusinessRuleException;
use App\Domain\Shared\NotFoundException;

final class ChangePreOrderStatus
{
    public function __construct(
        private PreOrderRepository $orders,
        private TransactionManager $tx,
        private AuditLogger $audit,
    ) {
    }

    public function execute(int $id, PreOrderStatus $next, int $actorId): void
    {
        $order = $this->orders->find($id) ?? throw NotFoundException::of('Pre-order');

        // Status pre-order yang ada di rencana produksi diatur otomatis oleh rencana tersebut.
        if ($this->orders->isUsedByOpenProductionPlan($id)) {
            throw new BusinessRuleException('Status pre-order ini diatur oleh rencana produksi yang sedang berjalan.');
        }

        $from = $order->status();
        $order->moveTo($next);

        $this->tx->run(function () use ($order, $id, $from, $next, $actorId) {
            $this->orders->save($order, $actorId);
            $this->audit->log($actorId, 'status', 'pre_order', $id, "Status pre-order diubah dari {$from->label()} ke {$next->label()}");
        });
    }
}
