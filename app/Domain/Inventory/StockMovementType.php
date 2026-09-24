<?php

namespace App\Domain\Inventory;

enum StockMovementType: string
{
    case In = 'in';
    case Production = 'production';
    case Waste = 'waste';
    case Adjustment = 'adjustment';

    public function label(): string
    {
        return match ($this) {
            self::In => 'Masuk',
            self::Production => 'Dipakai produksi',
            self::Waste => 'Rusak / kedaluwarsa',
            self::Adjustment => 'Koreksi stok',
        };
    }

    public function isOutgoing(): bool
    {
        return $this !== self::In;
    }

    /** Jenis keluar yang boleh dicatat manual oleh staf. */
    public static function manualOut(): array
    {
        return [self::Waste, self::Adjustment];
    }
}
