<?php

namespace App\Infrastructure\Persistence\Models;

use App\Domain\Auth\Permission;
use App\Domain\Auth\Role;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = ['name', 'email', 'password', 'role', 'is_active', 'last_login_at'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => Role::class,
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function hasPermission(Permission $permission): bool
    {
        return $this->is_active && $this->role->can($permission);
    }
}
