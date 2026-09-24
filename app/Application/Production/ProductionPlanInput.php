<?php

namespace App\Application\Production;

final class ProductionPlanInput
{
    public function __construct(
        public readonly string $planDate,
        public readonly ?string $notes,
        /** @var int[] pre-order berstatus Dikonfirmasi yang ikut diproduksi */
        public readonly array $preOrderIds,
        /** @var array<int, array{product_id:int|string, quantity:float|string}> produksi tambahan (stok toko, dll.) */
        public readonly array $extraItems,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            planDate: (string) $data['plan_date'],
            notes: $data['notes'] ?? null,
            preOrderIds: array_values(array_unique(array_map('intval', $data['pre_order_ids'] ?? []))),
            extraItems: array_values($data['items'] ?? []),
        );
    }
}
