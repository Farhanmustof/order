<?php

namespace App\Domain\Purchasing;

/** Status pemesanan barang ke supplier: Draft → Dipesan → Diterima, atau Dibatalkan. */
enum PurchaseStatus: string
{
    case Draft = 'draft';
    case Ordered = 'ordered';
    case Received = 'received';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Ordered => 'Dipesan',
            self::Received => 'Diterima',
            self::Cancelled => 'Dibatalkan',
        };
    }

    /** Urutan alur utama, dipakai untuk menampilkan tahapan. */
    public static function flow(): array
    {
        return [self::Draft, self::Ordered, self::Received];
    }

    /** Isi pesanan hanya boleh diubah selama masih Draft. */
    public function isEditable(): bool
    {
        return $this === self::Draft;
    }

    public function canOrder(): bool
    {
        return $this === self::Draft;
    }

    public function canReceive(): bool
    {
        return $this === self::Ordered;
    }

    public function canCancel(): bool
    {
        return in_array($this, [self::Draft, self::Ordered], true);
    }

    public function isDeletable(): bool
    {
        return in_array($this, [self::Draft, self::Cancelled], true);
    }
}
