<?php

namespace App\Application\Catalog;

use App\Application\Contracts\AuditLogger;
use App\Application\Contracts\TransactionManager;
use App\Domain\Catalog\ProductRepository;
use App\Domain\Shared\BusinessRuleException;
use App\Domain\Shared\NotFoundException;

final class DeleteProduct
{
    public function __construct(
        private ProductRepository $products,
        private TransactionManager $tx,
        private AuditLogger $audit,
    ) {
    }

    public function execute(int $id, int $actorId): void
    {
        if (! $this->products->exists($id)) {
            throw NotFoundException::of('Produk');
        }
        if ($this->products->isInUse($id)) {
            throw new BusinessRuleException('Produk masih dipakai pre-order aktif atau rencana produksi yang berjalan.');
        }
        $name = $this->products->names([$id])[$id] ?? '';

        $this->tx->run(function () use ($id, $name, $actorId) {
            $this->products->delete($id);
            $this->audit->log($actorId, 'delete', 'product', $id, "Menghapus produk {$name}");
        });
    }
}
