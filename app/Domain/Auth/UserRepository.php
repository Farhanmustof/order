<?php

namespace App\Domain\Auth;

interface UserRepository
{
    /** @return array{role: Role, is_active: bool}|null */
    public function find(int $id): ?array;

    /** @param string[]|null $permissions null = pakai hak bawaan level akun */
    public function create(string $name, string $email, string $plainPassword, Role $role, bool $isActive, ?array $permissions = null): int;

    /** @param string[]|null $permissions null = pakai hak bawaan level akun */
    public function update(int $id, string $name, string $email, Role $role, bool $isActive, ?string $plainPassword, ?array $permissions = null): void;

    public function changePassword(int $id, string $plainPassword): void;

    public function countActiveAdmins(?int $exceptUserId = null): int;
}
