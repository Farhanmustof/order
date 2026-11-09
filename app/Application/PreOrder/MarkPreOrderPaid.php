<?php

namespace App\Application\PreOrder;

use App\Application\Contracts\AuditLogger;
use App\Application\Contracts\TransactionManager;
use App\Domain\PreOrder\PreOrderRepository;
use App\Domain\Shared\NotFoundException;

/** Fitur tambahan: mencatat pelunasan pre-order. */
final class MarkPreOrderPaid
{
    public function __construct(
        private PreOrderRepository $orders,
        private TransactionManager $tx,
        private AuditLogger $audit,
    ) {
    }

    public function execute(int $id, int $actorId): float
    {
        $order = $this->orders->find($id) ?? throw NotFoundException::of('Pre-order');

        $paid = $order->remainingPayment();
        $order->markAsPaid();

        $this->tx->run(function () use ($order, $id, $paid, $actorId) {
            $this->orders->save($order, $actorId);
            $this->audit->log($actorId, 'update', 'pre_order', $id, 'Pelunasan pre-order dicatat sebesar '.rupiah($paid));
        });

        return $paid;
    }
}
