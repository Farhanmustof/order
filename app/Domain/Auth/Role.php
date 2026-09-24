<?php

namespace App\Domain\Auth;

/**
 * Level akun dan hak aksesnya.
 * Ubah daftar di permissions() bila aturan akses berubah.
 */
enum Role: string
{
    case Admin = 'admin';
    case User = 'user';
    case Direksi = 'direksi';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::User => 'User (Staf)',
            self::Direksi => 'Direksi',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Admin => 'Akses penuh, termasuk kelola akun, master data, dan pemulihan data terhapus.',
            self::User => 'Mencatat, mengubah, dan menghapus pre-order, rencana produksi, dan stok.',
            self::Direksi => 'Melihat semua data, laporan, dan riwayat aktivitas tanpa bisa mengubah.',
        };
    }

    /** @return Permission[] */
    public function permissions(): array
    {
        return match ($this) {
            self::Admin => Permission::cases(),
            self::User => [Permission::ViewData, Permission::ManageTransactions],
            self::Direksi => [Permission::ViewData, Permission::ViewAuditLog],
        };
    }

    public function can(Permission $permission): bool
    {
        return in_array($permission, $this->permissions(), true);
    }
}
