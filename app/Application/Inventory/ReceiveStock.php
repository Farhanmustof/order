<?php

namespace App\Application\Inventory;

use App\Application\Contracts\AuditLogger;
use App\Application\Contracts\TransactionManager;
use App\Domain\Inventory\RawMaterialRepository;
use App\Domain\Inventory\StockRepository;
use App\Domain\Shared\NotFoundException;

final class ReceiveStock
{
    public function __construct(
        private StockRepository $stock,
        private RawMaterialRepository $materials,
        private TransactionManager $tx,
        private AuditLogger $audit,
    ) {
    }

    public function execute(StockBatchInput $input, int $actorId): int
    {
        $info = $this->materials->info($input->rawMaterialId) ?? throw NotFoundException::of('Bahan baku');
        [$received, $expiry] = $input->dates();

        return $this->tx->run(function () use ($input, $received, $expiry, $info, $actorId) {
            $id = $this->stock->receive(
                $input->rawMaterialId, $input->supplierId, $input->batchCode, $received, $expiry,
                $input->quantity, $input->unitCost, $input->notes, $actorId,
            );
            $this->audit->log($actorId, 'create', 'stock_batch', $id, "Stok masuk {$info['name']} sebanyak {$input->quantity} {$info['unit']}");

            return $id;
        });
    }
}
