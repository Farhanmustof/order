<?php

namespace App\Application\Catalog;

use App\Application\Contracts\AuditLogger;
use App\Application\Contracts\TransactionManager;
use App\Domain\Catalog\SupplierDetails;
use App\Domain\Catalog\SupplierRepository;
use App\Domain\Shared\NotFoundException;

final class SaveSupplier
{
    public function __construct(
        private SupplierRepository $suppliers,
        private TransactionManager $tx,
        private AuditLogger $audit,
    ) {
    }

    public function execute(?int $id, array $data, int $actorId): int
    {
        if ($id !== null && ! $this->suppliers->exists($id)) {
            throw NotFoundException::of('Supplier');
        }
        $details = new SupplierDetails((string) $data['name'], $data['phone'] ?? null, $data['address'] ?? null, $data['notes'] ?? null);

        return $this->tx->run(function () use ($id, $details, $actorId) {
            $savedId = $this->suppliers->save($id, $details);
            $this->audit->log($actorId, $id ? 'update' : 'create', 'supplier', $savedId, ($id ? 'Mengubah' : 'Menambah')." supplier {$details->name}");

            return $savedId;
        });
    }
}
