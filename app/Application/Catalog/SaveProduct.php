<?php

namespace App\Application\Catalog;

use App\Application\Contracts\AuditLogger;
use App\Application\Contracts\TransactionManager;
use App\Domain\Catalog\ProductDetails;
use App\Domain\Catalog\ProductRepository;
use App\Domain\Catalog\RecipeLine;
use App\Domain\Shared\NotFoundException;

final class SaveProduct
{
    public function __construct(
        private ProductRepository $products,
        private TransactionManager $tx,
        private AuditLogger $audit,
    ) {
    }

    /** @param array<int, array{raw_material_id:int|string, quantity:float|string}> $recipe */
    public function execute(?int $id, array $data, array $recipe, int $actorId): int
    {
        if ($id !== null && ! $this->products->exists($id)) {
            throw NotFoundException::of('Produk');
        }

        $details = new ProductDetails(
            (string) $data['code'], (string) $data['name'], (string) $data['unit'],
            (float) $data['price'], $data['description'] ?? null,
        );
        $lines = array_map(fn ($r) => new RecipeLine((int) $r['raw_material_id'], (float) $r['quantity']), array_values($recipe));
        RecipeLine::ensureUnique($lines);

        return $this->tx->run(function () use ($id, $details, $lines, $actorId) {
            $savedId = $this->products->save($id, $details, $lines);
            $this->audit->log($actorId, $id ? 'update' : 'create', 'product', $savedId, ($id ? 'Mengubah' : 'Menambah')." produk {$details->name}");

            return $savedId;
        });
    }
}
