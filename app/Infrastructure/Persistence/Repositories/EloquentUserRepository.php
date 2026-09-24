<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Auth\Role;
use App\Domain\Auth\UserRepository;
use App\Infrastructure\Persistence\Models\User;

final class EloquentUserRepository implements UserRepository
{
    public function find(int $id): ?array
    {
        $user = User::find($id);

        return $user ? ['role' => $user->role, 'is_active' => $user->is_active] : null;
    }

    public function create(string $name, string $email, string $plainPassword, Role $role, bool $isActive): int
    {
        return User::create([
            'name' => $name,
            'email' => strtolower($email),
            'password' => $plainPassword, // di-hash otomatis oleh cast 'hashed'
            'role' => $role,
            'is_active' => $isActive,
        ])->id;
    }

    public function update(int $id, string $name, string $email, Role $role, bool $isActive, ?string $plainPassword): void
    {
        $user = User::findOrFail($id);
        $user->fill(['name' => $name, 'email' => strtolower($email), 'role' => $role, 'is_active' => $isActive]);
        if ($plainPassword) {
            $user->password = $plainPassword;
        }
        $user->save();
    }

    public function changePassword(int $id, string $plainPassword): void
    {
        User::whereKey($id)->firstOrFail()->forceFill(['password' => $plainPassword])->save();
    }

    public function countActiveAdmins(?int $exceptUserId = null): int
    {
        return User::where('role', Role::Admin->value)
            ->where('is_active', true)
            ->when($exceptUserId, fn ($q) => $q->whereKeyNot($exceptUserId))
            ->count();
    }
}
