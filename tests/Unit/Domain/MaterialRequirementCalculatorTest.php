<?php

namespace Tests\Unit\Domain;

use App\Domain\Auth\Permission;
use App\Domain\Auth\Role;
use App\Domain\Production\MaterialRequirementCalculator;
use PHPUnit\Framework\TestCase;

class MaterialRequirementCalculatorTest extends TestCase
{
    public function test_menjumlahkan_kebutuhan_bahan_dari_beberapa_produk(): void
    {
        $bom = [1 => [10 => 0.25, 11 => 0.1], 2 => [10 => 0.5]];

        $needs = (new MaterialRequirementCalculator())->calculate([1 => 10, 2 => 4], $bom);

        $this->assertSame([10 => 4.5, 11 => 1.0], $needs);
    }

    public function test_mendeteksi_produk_tanpa_resep(): void
    {
        $missing = (new MaterialRequirementCalculator())->productsWithoutRecipe([1 => 5, 3 => 2], [1 => [10 => 1]]);

        $this->assertSame([3], $missing);
    }

    public function test_hak_akses_per_level(): void
    {
        $this->assertTrue(Role::Admin->can(Permission::ManageUsers));
        $this->assertTrue(Role::User->can(Permission::ManageTransactions));
        $this->assertFalse(Role::User->can(Permission::ManageMasterData));
        $this->assertTrue(Role::Direksi->can(Permission::ViewData));
        $this->assertFalse(Role::Direksi->can(Permission::ManageTransactions));
    }
}
