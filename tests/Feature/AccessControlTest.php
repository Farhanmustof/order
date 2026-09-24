<?php

namespace Tests\Feature;

use App\Domain\Auth\Role;
use App\Infrastructure\Persistence\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    private function login(Role $role): static
    {
        return $this->actingAs(User::create([
            'name' => $role->label(), 'email' => $role->value.'@test.local',
            'password' => 'password123', 'role' => $role, 'is_active' => true,
        ]));
    }

    public function test_tamu_diarahkan_ke_halaman_masuk(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_direksi_hanya_bisa_melihat(): void
    {
        $this->login(Role::Direksi);

        $this->get(route('pre-orders.index'))->assertOk();
        $this->get(route('audit.index'))->assertOk();
        $this->get(route('pre-orders.create'))->assertForbidden();
        $this->post(route('pre-orders.store'))->assertForbidden();
    }

    public function test_user_bisa_transaksi_tapi_tidak_master_data(): void
    {
        $this->login(Role::User);

        $this->get(route('pre-orders.create'))->assertOk();
        $this->get(route('products.create'))->assertForbidden();
        $this->get(route('users.index'))->assertForbidden();
    }

    public function test_admin_bisa_semua(): void
    {
        $this->login(Role::Admin);

        $this->get(route('users.index'))->assertOk();
        $this->get(route('trash.index'))->assertOk();
        $this->get(route('products.create'))->assertOk();
    }
}
