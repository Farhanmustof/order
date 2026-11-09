<?php

namespace App\Domain\Purchasing;

use DateTimeImmutable;

interface PurchaseOrderRepository
{
    /**
     * @return array{number: string, supplier_id: int, status: PurchaseStatus,
     *               items: list<array{id: int, raw_material_id: int, quantity: float, unit_cost: float}>}|null
     */
    public function find(int $id): ?array;

    /**
     * @param  list<array{raw_material_id: int, quantity: float, unit_cost: float}>  $items
     * @return array{0: int, 1: string} id dan nomor pemesanan
     */
    public function create(int $supplierId, DateTimeImmutable $orderDate, ?DateTimeImmutable $expectedDate, ?string $notes, array $items, int $actorId): array;

    /** @param list<array{raw_material_id: int, quantity: float, unit_cost: float}> $items */
    public function update(int $id, int $supplierId, DateTimeImmutable $orderDate, ?DateTimeImmutable $expectedDate, ?string $notes, array $items, int $actorId): void;

    public function setStatus(int $id, PurchaseStatus $status, int $actorId, ?DateTimeImmutable $receivedDate = null): void;

    public function delete(int $id): void;
}
