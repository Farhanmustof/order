<?php

namespace App\Domain\PreOrder;

enum PreOrderStatus: string
{
    case Draft = 'draft';
    case Confirmed = 'confirmed';
    case InProduction = 'in_production';
    case Completed = 'completed';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Confirmed => 'Dikonfirmasi',
            self::InProduction => 'Diproduksi',
            self::Completed => 'Selesai',
            self::Delivered => 'Dikirim',
            self::Cancelled => 'Dibatalkan',
        };
    }

    /** Urutan alur utama, dipakai untuk menampilkan tahapan. */
    public static function flow(): array
    {
        return [self::Draft, self::Confirmed, self::InProduction, self::Completed, self::Delivered];
    }

    /** @return self[] */
    public function nextStatuses(): array
    {
        return match ($this) {
            self::Draft => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::InProduction, self::Cancelled],
            self::InProduction => [self::Completed],
            self::Completed => [self::Delivered],
            self::Delivered, self::Cancelled => [],
        };
    }

    public function canMoveTo(self $next): bool
    {
        return in_array($next, $this->nextStatuses(), true);
    }

    /** Item dan data pelanggan hanya boleh diubah sebelum produksi. */
    public function isEditable(): bool
    {
        return in_array($this, [self::Draft, self::Confirmed], true);
    }

    public function isDeletable(): bool
    {
        return in_array($this, [self::Draft, self::Confirmed, self::Cancelled], true);
    }

    /** Status yang masih berjalan (belum dikirim/dibatalkan). */
    public static function active(): array
    {
        return [self::Draft, self::Confirmed, self::InProduction, self::Completed];
    }
}
