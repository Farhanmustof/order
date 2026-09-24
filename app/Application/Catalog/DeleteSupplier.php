<?php

namespace App\Application\Catalog;

use App\Application\Contracts\AuditLogger;
use App\Application\Contracts\TransactionManager;
use App\Domain\Catalog\SupplierRepository;
use App\Domain\Shared\NotFoundException;

final class DeleteSupplier
{
    public function __construct(
        private SupplierRepository $suppliers,
        private TransactionManager $tx,
        private AuditLogger $audit,
    ) {
    }

    public function execute(int $id, int $actorId): void
    {
        if (! $this->suppliers->exists($id)) {
            throw NotFoundException::of('Supplier');
        }
        $this->tx->run(function () use ($id, $actorId) {
            $this->suppliers->delete($id);
            $this->audit->log($actorId, 'delete', 'supplier', $id, 'Menghapus supplier');
        });
    }
}
