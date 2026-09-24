<?php

namespace App\Application\Production;

use App\Domain\PreOrder\PreOrderRepository;
use App\Domain\PreOrder\PreOrderStatus;
use App\Domain\Production\ProductionPlanItem;
use App\Domain\Shared\BusinessRuleException;

/** Menyusun item rencana produksi dari pre-order terpilih dan produksi tambahan. */
final class PlanItemsBuilder
{
    public function __construct(private PreOrderRepository $orders)
    {
    }

    /** @return ProductionPlanItem[] */
    public function build(ProductionPlanInput $input, ?int $currentPlanId = null): array
    {
        $items = [];

        foreach ($input->preOrderIds as $preOrderId) {
            $order = $this->orders->find($preOrderId);
            if ($order === null) {
                throw new BusinessRuleException('Salah satu pre-order yang dipilih tidak ditemukan.');
            }
            if ($order->status() !== PreOrderStatus::Confirmed) {
                throw new BusinessRuleException("Pre-order untuk {$order->customerName()} belum/tidak berstatus Dikonfirmasi.");
            }
            if ($this->orders->isUsedByOpenProductionPlan($preOrderId, $currentPlanId)) {
                throw new BusinessRuleException("Pre-order untuk {$order->customerName()} sudah ada di rencana produksi lain.");
            }
            foreach ($order->items() as $orderItem) {
                $items[] = new ProductionPlanItem($orderItem->productId, $orderItem->quantity, $preOrderId);
            }
        }

        foreach ($input->extraItems as $row) {
            $items[] = new ProductionPlanItem((int) $row['product_id'], (float) $row['quantity']);
        }

        return $items;
    }
}
