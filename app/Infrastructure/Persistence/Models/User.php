<?php

namespace App\Infrastructure\Persistence\Models;

use App\Domain\Auth\Permission;
use App\Domain\Auth\Role;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = ['name', 'email', 'password', 'role', 'permissions', 'is_active', 'last_login_at'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => Role::class,
            'permissions' => 'array',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Hak akses yang berlaku untuk akun ini.
     * Admin selalu semua. Akun lain memakai centang yang disimpan,
     * atau hak bawaan level akunnya bila belum pernah diatur.
     *
     * @return string[]
     */
    public function permissionValues(): array
    {
        if ($this->role === Role::Admin) {
            return Role::Admin->permissionValues();
        }

        return $this->permissions ?? $this->role->permissionValues();
    }

    public function hasPermission(Permission $permission): bool
    {
        return $this->is_active && in_array($permission->value, $this->permissionValues(), true);
    }
}
