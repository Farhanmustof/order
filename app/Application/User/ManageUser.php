<?php

namespace App\Application\User;

use App\Application\Contracts\AuditLogger;
use App\Application\Contracts\TransactionManager;
use App\Domain\Auth\Role;
use App\Domain\Auth\UserRepository;
use App\Domain\Shared\BusinessRuleException;
use App\Domain\Shared\NotFoundException;

/** Membuat dan mengubah akun pengguna (khusus admin). */
final class ManageUser
{
    public function __construct(
        private UserRepository $users,
        private TransactionManager $tx,
        private AuditLogger $audit,
    ) {
    }

    public function create(string $name, string $email, string $password, Role $role, bool $isActive, int $actorId): int
    {
        return $this->tx->run(function () use ($name, $email, $password, $role, $isActive, $actorId) {
            $id = $this->users->create($name, $email, $password, $role, $isActive);
            $this->audit->log($actorId, 'create', 'user', $id, "Membuat akun {$name} sebagai {$role->label()}");

            return $id;
        });
    }

    public function update(int $id, string $name, string $email, Role $role, bool $isActive, ?string $password, int $actorId): void
    {
        $current = $this->users->find($id) ?? throw NotFoundException::of('Akun');

        if ($id === $actorId && ($role !== $current['role'] || ! $isActive)) {
            throw new BusinessRuleException('Anda tidak bisa mengubah level atau menonaktifkan akun Anda sendiri.');
        }

        $losesAdmin = $current['role'] === Role::Admin && $current['is_active']
            && ($role !== Role::Admin || ! $isActive);
        if ($losesAdmin && $this->users->countActiveAdmins($id) === 0) {
            throw new BusinessRuleException('Harus ada minimal satu admin aktif.');
        }

        $this->tx->run(function () use ($id, $name, $email, $role, $isActive, $password, $actorId) {
            $this->users->update($id, $name, $email, $role, $isActive, $password ?: null);
            $this->audit->log($actorId, 'update', 'user', $id, "Mengubah akun {$name}");
        });
    }

    public function changeOwnPassword(int $actorId, string $newPassword): void
    {
        $this->users->changePassword($actorId, $newPassword);
        $this->audit->log($actorId, 'update', 'user', $actorId, 'Mengganti kata sandi sendiri');
    }
}
