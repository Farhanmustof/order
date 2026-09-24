<?php

namespace App\Domain\PreOrder;

use App\Domain\Shared\BusinessRuleException;
use DateTimeImmutable;

/**
 * Entitas pre-order beserta aturan bisnisnya.
 * Tidak bergantung pada Laravel sama sekali.
 */
final class PreOrder
{
    /** @var PreOrderItem[] */
    private array $items = [];

    private function __construct(
        public readonly ?int $id,
        private string $customerName,
        private ?string $customerPhone,
        private ?string $customerAddress,
        private DateTimeImmutable $orderDate,
        private DateTimeImmutable $dueDate,
        private PreOrderStatus $status,
        private float $downPayment,
        private ?string $notes,
    ) {
    }

    /** @param PreOrderItem[] $items */
    public static function create(
        string $customerName,
        ?string $customerPhone,
        ?string $customerAddress,
        DateTimeImmutable $orderDate,
        DateTimeImmutable $dueDate,
        float $downPayment,
        ?string $notes,
        array $items,
    ): self {
        $order = new self(null, '', null, null, $orderDate, $dueDate, PreOrderStatus::Draft, 0, null);
        $order->fill($customerName, $customerPhone, $customerAddress, $orderDate, $dueDate, $downPayment, $notes, $items);

        return $order;
    }

    /** Dipakai repository untuk membangun ulang entitas dari database. */
    public static function restore(
        int $id,
        string $customerName,
        ?string $customerPhone,
        ?string $customerAddress,
        DateTimeImmutable $orderDate,
        DateTimeImmutable $dueDate,
        PreOrderStatus $status,
        float $downPayment,
        ?string $notes,
        array $items,
    ): self {
        $order = new self($id, $customerName, $customerPhone, $customerAddress, $orderDate, $dueDate, $status, $downPayment, $notes);
        $order->items = array_values($items);

        return $order;
    }

    /** @param PreOrderItem[] $items */
    public function update(
        string $customerName,
        ?string $customerPhone,
        ?string $customerAddress,
        DateTimeImmutable $orderDate,
        DateTimeImmutable $dueDate,
        float $downPayment,
        ?string $notes,
        array $items,
    ): void {
        if (! $this->status->isEditable()) {
            throw new BusinessRuleException(
                "Pre-order berstatus {$this->status->label()} tidak bisa diubah lagi."
            );
        }
        $this->fill($customerName, $customerPhone, $customerAddress, $orderDate, $dueDate, $downPayment, $notes, $items);
    }

    public function moveTo(PreOrderStatus $next): void
    {
        if (! $this->status->canMoveTo($next)) {
            throw new BusinessRuleException(
                "Status tidak bisa diubah dari {$this->status->label()} ke {$next->label()}."
            );
        }
        $this->status = $next;
    }

    /** Dipakai ketika rencana produksi yang memuat pre-order ini dibatalkan. */
    public function returnToConfirmed(): void
    {
        if ($this->status !== PreOrderStatus::InProduction) {
            throw new BusinessRuleException('Hanya pre-order yang sedang diproduksi yang bisa dikembalikan ke Dikonfirmasi.');
        }
        $this->status = PreOrderStatus::Confirmed;
    }

    public function ensureDeletable(): void
    {
        if (! $this->status->isDeletable()) {
            throw new BusinessRuleException(
                "Pre-order berstatus {$this->status->label()} tidak bisa dihapus."
            );
        }
    }

    private function fill(
        string $customerName,
        ?string $customerPhone,
        ?string $customerAddress,
        DateTimeImmutable $orderDate,
        DateTimeImmutable $dueDate,
        float $downPayment,
        ?string $notes,
        array $items,
    ): void {
        if (trim($customerName) === '') {
            throw new BusinessRuleException('Nama pelanggan wajib diisi.');
        }
        if ($dueDate < $orderDate) {
            throw new BusinessRuleException('Tanggal jatuh tempo tidak boleh sebelum tanggal pesan.');
        }
        if ($items === []) {
            throw new BusinessRuleException('Pre-order harus berisi minimal satu produk.');
        }
        $productIds = array_map(fn (PreOrderItem $i) => $i->productId, $items);
        if (count($productIds) !== count(array_unique($productIds))) {
            throw new BusinessRuleException('Produk yang sama tidak boleh dimasukkan dua kali. Gabungkan jumlahnya.');
        }

        $this->items = array_values($items);
        if ($downPayment < 0 || $downPayment > $this->total()) {
            throw new BusinessRuleException('DP harus antara 0 dan total pesanan.');
        }

        $this->customerName = trim($customerName);
        $this->customerPhone = $customerPhone;
        $this->customerAddress = $customerAddress;
        $this->orderDate = $orderDate;
        $this->dueDate = $dueDate;
        $this->downPayment = $downPayment;
        $this->notes = $notes;
    }

    public function total(): float
    {
        return round(array_sum(array_map(fn (PreOrderItem $i) => $i->subtotal(), $this->items)), 2);
    }

    public function remainingPayment(): float
    {
        return round($this->total() - $this->downPayment, 2);
    }

    public function status(): PreOrderStatus { return $this->status; }
    public function customerName(): string { return $this->customerName; }
    public function customerPhone(): ?string { return $this->customerPhone; }
    public function customerAddress(): ?string { return $this->customerAddress; }
    public function orderDate(): DateTimeImmutable { return $this->orderDate; }
    public function dueDate(): DateTimeImmutable { return $this->dueDate; }
    public function downPayment(): float { return $this->downPayment; }
    public function notes(): ?string { return $this->notes; }
    /** @return PreOrderItem[] */
    public function items(): array { return $this->items; }
}
