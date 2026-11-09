<?php

namespace App\Http\Controllers;

use App\Application\User\ManageUser;
use App\Domain\Auth\Permission;
use App\Domain\Auth\Role;
use App\Infrastructure\Persistence\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        return view('users.index', ['users' => User::orderBy('role')->orderBy('name')->get(), 'roles' => Role::cases()]);
    }

    public function create()
    {
        return view('users.form', ['user' => null, 'roles' => Role::cases(), 'menus' => Permission::menus()]);
    }

    public function store(Request $request, ManageUser $useCase)
    {
        $data = $this->validated($request);
        $useCase->create(
            $data['name'], $data['email'], $data['password'], Role::from($data['role']),
            $request->boolean('is_active'), $this->actorId(), $this->permissionsFrom($request),
        );

        return redirect()->route('users.index')->with('success', 'Akun dibuat. Berikan email dan kata sandinya kepada pemilik akun.');
    }

    public function edit(int $id)
    {
        return view('users.form', ['user' => User::findOrFail($id), 'roles' => Role::cases(), 'menus' => Permission::menus()]);
    }

    public function update(Request $request, int $id, ManageUser $useCase)
    {
        $data = $this->validated($request, $id);
        $useCase->update(
            $id, $data['name'], $data['email'], Role::from($data['role']),
            $request->boolean('is_active'), $data['password'] ?? null, $this->actorId(), $this->permissionsFrom($request),
        );

        return redirect()->route('users.index')->with('success', 'Akun diperbarui.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'role' => ['required', Rule::enum(Role::class)],
            'password' => [$id ? 'nullable' : 'required', 'confirmed', Password::min(8)],
            'permissions' => ['array'],
            'permissions.*' => [Rule::enum(Permission::class)],
        ]);
    }

    /** Centang hak akses dari form. Hak "kelola" otomatis ikut membawa hak "lihat". */
    private function permissionsFrom(Request $request): array
    {
        return Permission::normalize($request->input('permissions', []));
    }
}
