<?php

namespace App\Domain\Production;

enum ProductionStatus: string
{
    case Planned = 'planned';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Direncanakan',
            self::InProgress => 'Sedang produksi',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public static function flow(): array
    {
        return [self::Planned, self::InProgress, self::Completed];
    }

    public function isOpen(): bool
    {
        return in_array($this, [self::Planned, self::InProgress], true);
    }
}
