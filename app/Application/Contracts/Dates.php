<?php

namespace App\Application\Contracts;

use App\Domain\Shared\BusinessRuleException;
use DateTimeImmutable;

final class Dates
{
    public static function parse(?string $value, string $label = 'Tanggal'): ?DateTimeImmutable
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if ($date === false) {
            throw new BusinessRuleException("{$label} tidak valid.");
        }

        return $date;
    }

    public static function required(?string $value, string $label = 'Tanggal'): DateTimeImmutable
    {
        return self::parse($value, $label) ?? throw new BusinessRuleException("{$label} wajib diisi.");
    }
}
