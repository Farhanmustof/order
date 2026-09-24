<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(UserSeeder::class);

        // Data contoh usaha roti & kue. Hapus baris ini bila ingin mulai dari database kosong.
        $this->call(DemoDataSeeder::class);
    }
}
