<?php

namespace App\Domain\Auth;

interface UserRepository
{
    /** @return array{role: Role, is_active: bool}|null */
    public function find(int $id): ?array;

    public function create(string $name, string $email, string $plainPassword, Role $role, bool $isActive): int;

    public function update(int $id, string $name, string $email, Role $role, bool $isActive, ?string $plainPassword): void;

    public function changePassword(int $id, string $plainPassword): void;

    public function countActiveAdmins(?int $exceptUserId = null): int;
}
