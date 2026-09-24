<?php

namespace App\Domain\Production;

use App\Domain\Shared\BusinessRuleException;
use DateTimeImmutable;

final class ProductionPlan
{
    /** @var ProductionPlanItem[] */
    private array $items = [];

    private function __construct(
        public readonly ?int $id,
        private DateTimeImmutable $planDate,
        private ProductionStatus $status,
        private ?string $notes,
    ) {
    }

    /** @param ProductionPlanItem[] $items */
    public static function create(DateTimeImmutable $planDate, ?string $notes, array $items): self
    {
        $plan = new self(null, $planDate, ProductionStatus::Planned, null);
        $plan->fill($planDate, $notes, $items);

        return $plan;
    }

    public static function restore(int $id, DateTimeImmutable $planDate, ProductionStatus $status, ?string $notes, array $items): self
    {
        $plan = new self($id, $planDate, $status, $notes);
        $plan->items = array_values($items);

        return $plan;
    }

    /** @param ProductionPlanItem[] $items */
    public function update(DateTimeImmutable $planDate, ?string $notes, array $items): void
    {
        if ($this->status !== ProductionStatus::Planned) {
            throw new BusinessRuleException('Hanya rencana berstatus Direncanakan yang bisa diubah.');
        }
        $this->fill($planDate, $notes, $items);
    }

    public function start(): void
    {
        if ($this->status !== ProductionStatus::Planned) {
            throw new BusinessRuleException('Produksi hanya bisa dimulai dari status Direncanakan.');
        }
        $this->status = ProductionStatus::InProgress;
    }

    public function complete(): void
    {
        if ($this->status !== ProductionStatus::InProgress) {
            throw new BusinessRuleException('Mulai produksi terlebih dahulu sebelum menandainya selesai.');
        }
        $this->status = ProductionStatus::Completed;
    }

    public function cancel(): void
    {
        if (! $this->status->isOpen()) {
            throw new BusinessRuleException("Rencana berstatus {$this->status->label()} tidak bisa dibatalkan.");
        }
        $this->status = ProductionStatus::Cancelled;
    }

    public function ensureDeletable(): void
    {
        if (! in_array($this->status, [ProductionStatus::Planned, ProductionStatus::Cancelled], true)) {
            throw new BusinessRuleException(
                "Rencana berstatus {$this->status->label()} tidak bisa dihapus karena sudah memengaruhi stok atau pesanan."
            );
        }
    }

    private function fill(DateTimeImmutable $planDate, ?string $notes, array $items): void
    {
        if ($items === []) {
            throw new BusinessRuleException('Rencana produksi harus berisi minimal satu produk.');
        }
        $this->planDate = $planDate;
        $this->notes = $notes;
        $this->items = array_values($items);
    }

    /** Total jumlah per produk (produk sama dari beberapa pre-order digabung). */
    public function quantitiesByProduct(): array
    {
        $totals = [];
        foreach ($this->items as $item) {
            $totals[$item->productId] = round(($totals[$item->productId] ?? 0) + $item->quantity, 3);
        }

        return $totals;
    }

    /** @return int[] */
    public function preOrderIds(): array
    {
        return array_values(array_unique(array_filter(array_map(fn ($i) => $i->preOrderId, $this->items))));
    }

    public function status(): ProductionStatus { return $this->status; }
    public function planDate(): DateTimeImmutable { return $this->planDate; }
    public function notes(): ?string { return $this->notes; }
    /** @return ProductionPlanItem[] */
    public function items(): array { return $this->items; }
}
