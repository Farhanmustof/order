<?php

namespace Database\Seeders;

use App\Domain\Auth\Role;
use App\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Seeder;

/**
 * Akun awal. SEGERA ganti kata sandinya setelah login pertama
 * (menu Akun Saya), terutama sebelum web di-online-kan.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['Administrator', 'admin@example.com', Role::Admin],
            ['Staf Produksi', 'user@example.com', Role::User],
            ['Direksi', 'direksi@example.com', Role::Direksi],
        ];

        foreach ($accounts as [$name, $email, $role]) {
            User::updateOrCreate(['email' => $email], [
                'name' => $name,
                'password' => 'password123',
                'role' => $role,
                'is_active' => true,
            ]);
        }
    }
}
