<?php

namespace App\Application\Contracts;

interface TransactionManager
{
    /**
     * Menjalankan $work dalam satu transaksi database.
     * Bila terjadi error, semua perubahan dibatalkan.
     */
    public function run(callable $work): mixed;
}
