<?php

namespace App\Application\PreOrder;

use App\Application\Contracts\AuditLogger;
use App\Application\Contracts\TransactionManager;
use App\Domain\PreOrder\PreOrderRepository;
use App\Domain\Shared\BusinessRuleException;
use App\Domain\Shared\NotFoundException;

final class UpdatePreOrder
{
    public function __construct(
        private PreOrderRepository $orders,
        private TransactionManager $tx,
        private AuditLogger $audit,
    ) {
    }

    public function execute(int $id, PreOrderInput $input, int $actorId): void
    {
        $order = $this->orders->find($id) ?? throw NotFoundException::of('Pre-order');

        if ($this->orders->isUsedByOpenProductionPlan($id)) {
            throw new BusinessRuleException('Pre-order ini sudah masuk rencana produksi. Keluarkan dulu dari rencana produksi sebelum mengubahnya.');
        }

        $order->update(
            $input->customerName,
            $input->customerPhone,
            $input->customerAddress,
            $input->orderDate(),
            $input->dueDate(),
            $input->downPayment,
            $input->notes,
            $input->domainItems(),
        );

        $this->tx->run(function () use ($order, $id, $actorId) {
            $this->orders->save($order, $actorId);
            $this->audit->log($actorId, 'update', 'pre_order', $id, "Mengubah pre-order {$order->customerName()}");
        });
    }
}
