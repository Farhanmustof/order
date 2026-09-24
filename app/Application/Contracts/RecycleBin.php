<?php

namespace App\Application\Contracts;

interface RecycleBin
{
    /** Jenis data yang bisa dipulihkan, [kunci => label]. */
    public function types(): array;

    /** Memulihkan data terhapus, mengembalikan nama/nomor datanya. */
    public function restore(string $type, int $id): string;
}
