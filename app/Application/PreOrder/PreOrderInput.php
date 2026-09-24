<?php

namespace App\Application\PreOrder;

use App\Application\Contracts\Dates;
use App\Domain\PreOrder\PreOrderItem;

/** Data mentah dari form pre-order yang sudah lolos validasi format. */
final class PreOrderInput
{
    public function __construct(
        public readonly string $customerName,
        public readonly ?string $customerPhone,
        public readonly ?string $customerAddress,
        public readonly string $orderDate,
        public readonly string $dueDate,
        public readonly float $downPayment,
        public readonly ?string $notes,
        /** @var array<int, array{product_id:int|string, quantity:float|string, unit_price:float|string}> */
        public readonly array $items,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            customerName: (string) $data['customer_name'],
            customerPhone: $data['customer_phone'] ?? null,
            customerAddress: $data['customer_address'] ?? null,
            orderDate: (string) $data['order_date'],
            dueDate: (string) $data['due_date'],
            downPayment: (float) ($data['down_payment'] ?? 0),
            notes: $data['notes'] ?? null,
            items: array_values($data['items'] ?? []),
        );
    }

    /** @return PreOrderItem[] */
    public function domainItems(): array
    {
        return array_map(
            fn (array $row) => new PreOrderItem((int) $row['product_id'], (float) $row['quantity'], (float) $row['unit_price']),
            $this->items,
        );
    }

    public function orderDate(): \DateTimeImmutable
    {
        return Dates::required($this->orderDate, 'Tanggal pesan');
    }

    public function dueDate(): \DateTimeImmutable
    {
        return Dates::required($this->dueDate, 'Tanggal jatuh tempo');
    }
}
