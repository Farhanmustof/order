<?php

namespace App\Infrastructure\Services;

use App\Application\Contracts\Clock;
use DateTimeImmutable;

final class SystemClock implements Clock
{
    public function today(): DateTimeImmutable
    {
        return DateTimeImmutable::createFromInterface(today());
    }
}
