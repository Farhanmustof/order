<?php

namespace App\Infrastructure\Services;

use App\Application\Contracts\TransactionManager;
use Illuminate\Support\Facades\DB;

final class DatabaseTransactionManager implements TransactionManager
{
    public function run(callable $work): mixed
    {
        return DB::transaction($work);
    }
}
