<?php

namespace App\Application\Catalog;

use App\Application\Contracts\AuditLogger;
use App\Application\Contracts\TransactionManager;
use App\Domain\Inventory\RawMaterialRepository;
use App\Domain\Shared\BusinessRuleException;
use App\Domain\Shared\NotFoundException;

final class DeleteRawMaterial
{
    public function __construct(
        private RawMaterialRepository $materials,
        private TransactionManager $tx,
        private AuditLogger $audit,
    ) {
    }

    public function execute(int $id, int $actorId): void
    {
        $info = $this->materials->info($id) ?? throw NotFoundException::of('Bahan baku');
        if ($this->materials->hasStock($id)) {
            throw new BusinessRuleException("{$info['name']} masih punya stok. Habiskan atau catat stok keluar terlebih dahulu.");
        }
        if ($this->materials->isUsedInRecipe($id)) {
            throw new BusinessRuleException("{$info['name']} masih dipakai di resep produk. Hapus dari resep terlebih dahulu.");
        }

        $this->tx->run(function () use ($id, $info, $actorId) {
            $this->materials->delete($id);
            $this->audit->log($actorId, 'delete', 'raw_material', $id, "Menghapus bahan baku {$info['name']}");
        });
    }
}
