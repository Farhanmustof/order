<?php

namespace App\Domain\Shared;

class NotFoundException extends BusinessRuleException
{
    public static function of(string $what): self
    {
        return new self("{$what} tidak ditemukan atau sudah dihapus.");
    }
}
