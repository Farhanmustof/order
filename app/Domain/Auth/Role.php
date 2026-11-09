<?php

namespace App\Domain\Auth;

/**
 * Level akun dan hak akses bawaannya.
 * Admin selalu punya semua hak akses. Untuk User dan Direksi, daftar di
 * permissions() hanya dipakai sebagai centang awal; Admin bisa mengubahnya per akun.
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
            self::Admin => 'Akses penuh ke semua menu. Hak aksesnya tidak bisa dikurangi.',
            self::User => 'Mencatat pre-order, rencana produksi, dan stok. Hak akses bisa diatur per menu.',
            self::Direksi => 'Melihat semua data dan riwayat aktivitas. Hak akses bisa diatur per menu.',
        };
    }

    /** @return Permission[] hak akses bawaan */
    public function permissions(): array
    {
        return match ($this) {
            self::Admin => Permission::cases(),
            self::User => [
                Permission::PreOrderView, Permission::PreOrderManage,
                Permission::ProductionView, Permission::ProductionManage,
                Permission::StockView, Permission::StockManage,
                Permission::PurchaseView, Permission::PurchaseManage,
                Permission::ProductView, Permission::SupplierView,
            ],
            self::Direksi => [
                Permission::PreOrderView, Permission::ProductionView, Permission::StockView,
                Permission::PurchaseView, Permission::ProductView, Permission::SupplierView, Permission::AuditView,
            ],
        };
    }

    /** @return string[] nilai hak akses bawaan, untuk disimpan atau dicentang di form */
    public function permissionValues(): array
    {
        return array_map(fn (Permission $p) => $p->value, $this->permissions());
    }

    public function can(Permission $permission): bool
    {
        return in_array($permission, $this->permissions(), true);
    }
}
