<?php

namespace App\Application\Catalog;

use App\Application\Contracts\AuditLogger;
use App\Application\Contracts\TransactionManager;
use App\Domain\Inventory\RawMaterialDetails;
use App\Domain\Inventory\RawMaterialRepository;
use App\Domain\Shared\NotFoundException;

final class SaveRawMaterial
{
    public function __construct(
        private RawMaterialRepository $materials,
        private TransactionManager $tx,
        private AuditLogger $audit,
    ) {
    }

    public function execute(?int $id, array $data, int $actorId): int
    {
        if ($id !== null && $this->materials->info($id) === null) {
            throw NotFoundException::of('Bahan baku');
        }
        $details = new RawMaterialDetails(
            (string) $data['code'], (string) $data['name'], (string) $data['unit'],
            (float) ($data['minimum_stock'] ?? 0), $data['notes'] ?? null,
        );

        return $this->tx->run(function () use ($id, $details, $actorId) {
            $savedId = $this->materials->save($id, $details);
            $this->audit->log($actorId, $id ? 'update' : 'create', 'raw_material', $savedId, ($id ? 'Mengubah' : 'Menambah')." bahan baku {$details->name}");

            return $savedId;
        });
    }
}
