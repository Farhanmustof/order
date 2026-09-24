<?php

namespace App\Application\PreOrder;

use App\Application\Contracts\AuditLogger;
use App\Application\Contracts\TransactionManager;
use App\Domain\PreOrder\PreOrder;
use App\Domain\PreOrder\PreOrderRepository;

final class CreatePreOrder
{
    public function __construct(
        private PreOrderRepository $orders,
        private TransactionManager $tx,
        private AuditLogger $audit,
    ) {
    }

    public function execute(PreOrderInput $input, int $actorId): int
    {
        $order = PreOrder::create(
            $input->customerName,
            $input->customerPhone,
            $input->customerAddress,
            $input->orderDate(),
            $input->dueDate(),
            $input->downPayment,
            $input->notes,
            $input->domainItems(),
        );

        return $this->tx->run(function () use ($order, $actorId) {
            $id = $this->orders->save($order, $actorId);
            $this->audit->log($actorId, 'create', 'pre_order', $id, "Membuat pre-order untuk {$order->customerName()}");

            return $id;
        });
    }
}
