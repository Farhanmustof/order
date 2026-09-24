<?php

namespace Database\Seeders;

use App\Application\Catalog\SaveProduct;
use App\Application\Catalog\SaveRawMaterial;
use App\Application\Catalog\SaveSupplier;
use App\Application\Inventory\ReceiveStock;
use App\Application\Inventory\StockBatchInput;
use App\Application\PreOrder\ChangePreOrderStatus;
use App\Application\PreOrder\CreatePreOrder;
use App\Application\PreOrder\PreOrderInput;
use App\Application\Production\CompleteProduction;
use App\Application\Production\CreateProductionPlan;
use App\Application\Production\ProductionPlanInput;
use App\Application\Production\StartProduction;
use App\Domain\PreOrder\PreOrderStatus;
use App\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Seeder;

/**
 * Data contoh untuk usaha roti & kue.
 * Sengaja dibuat lewat use case (bukan insert langsung) sehingga semua aturan bisnis ikut teruji.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->value('id');
        $day = fn (int $offset) => today()->addDays($offset)->toDateString();

        $supplier = app(SaveSupplier::class);
        $s = [
            'tepung' => $supplier->execute(null, ['name' => 'CV Sumber Tepung', 'phone' => '0812-1111-2222', 'address' => 'Jl. Pasar Baru No. 12'], $admin),
            'telur' => $supplier->execute(null, ['name' => 'UD Telur Makmur', 'phone' => '0813-3333-4444', 'address' => 'Jl. Peternakan No. 5'], $admin),
            'susu' => $supplier->execute(null, ['name' => 'PT Susu Segar Nusantara', 'phone' => '021-555-0101'], $admin),
        ];

        $material = app(SaveRawMaterial::class);
        $m = [];
        foreach ([
            ['TPG', 'Tepung terigu', 'kg', 20],
            ['GUL', 'Gula pasir', 'kg', 10],
            ['TLR', 'Telur ayam', 'butir', 60],
            ['MTG', 'Mentega', 'kg', 5],
            ['DCC', 'Cokelat masak (DCC)', 'kg', 4],
            ['CKB', 'Cokelat bubuk', 'kg', 2],
            ['SSU', 'Susu cair', 'liter', 10],
            ['KJU', 'Keju cheddar', 'kg', 3],
            ['RGI', 'Ragi instan', 'gram', 200],
        ] as [$code, $name, $unit, $min]) {
            $m[$code] = $material->execute(null, ['code' => $code, 'name' => $name, 'unit' => $unit, 'minimum_stock' => $min], $admin);
        }

        $product = app(SaveProduct::class);
        $recipe = fn (array $lines) => array_map(fn ($code, $qty) => ['raw_material_id' => $m[$code], 'quantity' => $qty], array_keys($lines), $lines);
        $p = [
            'RTC' => $product->execute(null, ['code' => 'RTC', 'name' => 'Roti cokelat', 'unit' => 'pcs', 'price' => 8000],
                $recipe(['TPG' => 0.06, 'GUL' => 0.01, 'TLR' => 0.2, 'MTG' => 0.005, 'DCC' => 0.015, 'SSU' => 0.02, 'RGI' => 1.5]), $admin),
            'BRW' => $product->execute(null, ['code' => 'BRW', 'name' => 'Brownies panggang', 'unit' => 'loyang', 'price' => 85000],
                $recipe(['TPG' => 0.15, 'GUL' => 0.2, 'TLR' => 4, 'MTG' => 0.15, 'DCC' => 0.2, 'CKB' => 0.03]), $admin),
            'BLK' => $product->execute(null, ['code' => 'BLK', 'name' => 'Bolu keju', 'unit' => 'loyang', 'price' => 95000],
                $recipe(['TPG' => 0.2, 'GUL' => 0.2, 'TLR' => 6, 'MTG' => 0.2, 'KJU' => 0.15, 'SSU' => 0.1]), $admin),
            'RTW' => $product->execute(null, ['code' => 'RTW', 'name' => 'Roti tawar', 'unit' => 'bungkus', 'price' => 18000],
                $recipe(['TPG' => 0.35, 'GUL' => 0.02, 'MTG' => 0.02, 'SSU' => 0.05, 'RGI' => 5]), $admin),
        ];

        $receive = app(ReceiveStock::class);
        foreach ([
            ['TPG', 'tepung', -20, 120, 50, 12500],
            ['TPG', 'tepung', -3, 150, 25, 12800],
            ['GUL', 'tepung', -15, 365, 30, 16500],
            ['TLR', 'telur', -4, 10, 150, 2000],
            ['TLR', 'telur', -12, -2, 30, 1900],
            ['MTG', 'susu', -10, 60, 8, 95000],
            ['DCC', 'tepung', -30, 180, 6, 78000],
            ['CKB', 'tepung', -30, 200, 3, 110000],
            ['SSU', 'susu', -2, 5, 20, 18000],
            ['KJU', 'susu', -8, 25, 2, 120000],
            ['RGI', 'tepung', -40, 90, 1000, 90],
        ] as [$code, $sup, $received, $expires, $qty, $cost]) {
            $receive->execute(StockBatchInput::fromArray([
                'raw_material_id' => $m[$code],
                'supplier_id' => $s[$sup],
                'batch_code' => $code.'-'.today()->addDays($received)->format('ymd'),
                'received_date' => $day($received),
                'expiry_date' => $day($expires),
                'quantity' => $qty,
                'unit_cost' => $cost,
            ]), $admin);
        }

        $create = app(CreatePreOrder::class);
        $status = app(ChangePreOrderStatus::class);
        $order = function (string $name, string $phone, int $orderOffset, int $dueOffset, float $dp, array $items, array $statuses = []) use ($create, $status, $admin, $p, $day) {
            $lines = [];
            foreach ($items as $code => [$quantity, $price]) {
                $lines[] = ['product_id' => $p[$code], 'quantity' => $quantity, 'unit_price' => $price];
            }
            $id = $create->execute(PreOrderInput::fromArray([
                'customer_name' => $name,
                'customer_phone' => $phone,
                'order_date' => $day($orderOffset),
                'due_date' => $day($dueOffset),
                'down_payment' => $dp,
                'items' => $lines,
            ]), $admin);
            foreach ($statuses as $next) {
                $status->execute($id, $next, $admin);
            }

            return $id;
        };

        $po1 = $order('Ibu Ratna (arisan RT 05)', '0812-7777-1010', -6, -1, 200000, ['RTC' => [60, 8000], 'BRW' => [2, 85000]], [PreOrderStatus::Confirmed]);
        $po2 = $order('Kantor Kelurahan Sukamaju', '021-777-2020', -4, 2, 500000, ['RTC' => [100, 7500], 'RTW' => [10, 18000]], [PreOrderStatus::Confirmed]);
        $po3 = $order('Bapak Hendra', '0857-1234-5678', -2, 4, 100000, ['BLK' => [3, 95000]], [PreOrderStatus::Confirmed]);
        $order('Kafe Senja', '0819-4444-9090', -1, 6, 0, ['BRW' => [5, 80000], 'BLK' => [2, 95000]]);
        $order('Ibu Lestari', '0811-2323-4545', 0, 9, 50000, ['RTW' => [6, 18000]]);

        // Rencana yang sudah selesai: stok bahan otomatis terpotong dengan aturan FEFO.
        $plans = app(CreateProductionPlan::class);
        $done = $plans->execute(ProductionPlanInput::fromArray(['plan_date' => $day(-1), 'pre_order_ids' => [$po1]]), $admin);
        app(StartProduction::class)->execute($done, $admin);
        app(CompleteProduction::class)->execute($done, $admin);
        $status->execute($po1, PreOrderStatus::Delivered, $admin);

        // Rencana untuk besok, masih direncanakan.
        $plans->execute(ProductionPlanInput::fromArray([
            'plan_date' => $day(1),
            'notes' => 'Tambah 20 roti tawar untuk stok etalase.',
            'pre_order_ids' => [$po2, $po3],
            'items' => [['product_id' => $p['RTW'], 'quantity' => 20]],
        ]), $admin);
    }
}
