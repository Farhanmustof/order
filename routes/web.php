<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PreOrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductionPlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\RawMaterialController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\StockOutController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TrashController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
| Hak akses diatur lewat middleware "can:<hak akses>".
| Daftar hak akses ada di app/Domain/Auth/Permission.php.
| Admin selalu punya semua hak; akun lain diatur per menu di form Pengguna.
*/

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/masuk', [AuthController::class, 'login'])->middleware('throttle:6,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/keluar', [AuthController::class, 'logout'])->name('logout');
    Route::get('/akun-saya', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/akun-saya/kata-sandi', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::get('/', DashboardController::class)->name('dashboard');

    // ── Pre-order ──────────────────────────────────────────────────
    Route::middleware('can:pre-order.lihat')->group(function () {
        Route::get('/pre-order', [PreOrderController::class, 'index'])->name('pre-orders.index');
        Route::get('/pre-order/ekspor', [PreOrderController::class, 'export'])->name('pre-orders.export');
        Route::get('/pre-order/{id}', [PreOrderController::class, 'show'])->whereNumber('id')->name('pre-orders.show');
    });
    Route::middleware('can:pre-order.kelola')->group(function () {
        Route::get('/pre-order/baru', [PreOrderController::class, 'create'])->name('pre-orders.create');
        Route::post('/pre-order', [PreOrderController::class, 'store'])->name('pre-orders.store');
        Route::get('/pre-order/{id}/ubah', [PreOrderController::class, 'edit'])->whereNumber('id')->name('pre-orders.edit');
        Route::put('/pre-order/{id}', [PreOrderController::class, 'update'])->whereNumber('id')->name('pre-orders.update');
        Route::patch('/pre-order/{id}/status', [PreOrderController::class, 'changeStatus'])->whereNumber('id')->name('pre-orders.status');
        Route::delete('/pre-order/{id}', [PreOrderController::class, 'destroy'])->whereNumber('id')->name('pre-orders.destroy');
        Route::patch('/pre-order/{id}/lunas', [PreOrderController::class, 'markPaid'])->whereNumber('id')->name('pre-orders.paid'); // fitur tambahan
    });

    // ── Rencana produksi ───────────────────────────────────────────
    Route::middleware('can:produksi.lihat')->group(function () {
        Route::get('/produksi', [ProductionPlanController::class, 'index'])->name('production.index');
        Route::get('/produksi/{id}', [ProductionPlanController::class, 'show'])->whereNumber('id')->name('production.show');
    });
    Route::middleware('can:produksi.kelola')->group(function () {
        Route::get('/produksi/baru', [ProductionPlanController::class, 'create'])->name('production.create');
        Route::post('/produksi', [ProductionPlanController::class, 'store'])->name('production.store');
        Route::get('/produksi/{id}/ubah', [ProductionPlanController::class, 'edit'])->whereNumber('id')->name('production.edit');
        Route::put('/produksi/{id}', [ProductionPlanController::class, 'update'])->whereNumber('id')->name('production.update');
        Route::patch('/produksi/{id}/mulai', [ProductionPlanController::class, 'start'])->whereNumber('id')->name('production.start');
        Route::patch('/produksi/{id}/selesai', [ProductionPlanController::class, 'complete'])->whereNumber('id')->name('production.complete');
        Route::patch('/produksi/{id}/batal', [ProductionPlanController::class, 'cancel'])->whereNumber('id')->name('production.cancel');
        Route::delete('/produksi/{id}', [ProductionPlanController::class, 'destroy'])->whereNumber('id')->name('production.destroy');
    });

    // ── Stok bahan baku & riwayat stok ─────────────────────────────
    Route::middleware('can:stok.lihat')->group(function () {
        Route::get('/stok', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/stok/ekspor', [InventoryController::class, 'export'])->name('inventory.export');
        Route::get('/stok/riwayat', [InventoryController::class, 'movements'])->name('inventory.movements');
        Route::get('/stok/riwayat/ekspor', [InventoryController::class, 'exportMovements'])->name('inventory.movements.export');
        Route::get('/stok/{id}', [InventoryController::class, 'show'])->whereNumber('id')->name('inventory.show');
    });
    Route::middleware('can:stok.kelola')->group(function () {
        Route::get('/stok/masuk', [StockInController::class, 'create'])->name('stock-in.create');
        Route::post('/stok/masuk', [StockInController::class, 'store'])->name('stock-in.store');
        Route::get('/stok/masuk/{id}/ubah', [StockInController::class, 'edit'])->whereNumber('id')->name('stock-in.edit');
        Route::put('/stok/masuk/{id}', [StockInController::class, 'update'])->whereNumber('id')->name('stock-in.update');
        Route::delete('/stok/masuk/{id}', [StockInController::class, 'destroy'])->whereNumber('id')->name('stock-in.destroy');
        Route::get('/stok/keluar', [StockOutController::class, 'create'])->name('stock-out.create');
        Route::post('/stok/keluar', [StockOutController::class, 'store'])->name('stock-out.store');
    });

    // ── Pemesanan ke supplier ──────────────────────────────────────
    Route::middleware('can:pembelian.lihat')->group(function () {
        Route::get('/pembelian', [PurchaseOrderController::class, 'index'])->name('purchases.index');
        Route::get('/pembelian/{id}', [PurchaseOrderController::class, 'show'])->whereNumber('id')->name('purchases.show');
    });
    Route::middleware('can:pembelian.kelola')->group(function () {
        Route::get('/pembelian/baru', [PurchaseOrderController::class, 'create'])->name('purchases.create');
        Route::post('/pembelian', [PurchaseOrderController::class, 'store'])->name('purchases.store');
        Route::get('/pembelian/{id}/ubah', [PurchaseOrderController::class, 'edit'])->whereNumber('id')->name('purchases.edit');
        Route::put('/pembelian/{id}', [PurchaseOrderController::class, 'update'])->whereNumber('id')->name('purchases.update');
        Route::patch('/pembelian/{id}/dipesan', [PurchaseOrderController::class, 'markOrdered'])->whereNumber('id')->name('purchases.ordered');
        Route::post('/pembelian/{id}/terima', [PurchaseOrderController::class, 'receive'])->whereNumber('id')->name('purchases.receive');
        Route::patch('/pembelian/{id}/batal', [PurchaseOrderController::class, 'cancel'])->whereNumber('id')->name('purchases.cancel');
        Route::delete('/pembelian/{id}', [PurchaseOrderController::class, 'destroy'])->whereNumber('id')->name('purchases.destroy');
    });

    // ── Produk, resep & bahan baku ─────────────────────────────────
    Route::get('/produk', [ProductController::class, 'index'])->middleware('can:produk.lihat')->name('products.index');
    Route::middleware('can:produk.kelola')->group(function () {
        Route::resource('produk', ProductController::class)
            ->except(['index', 'show'])->parameters(['produk' => 'id'])->names('products');
        Route::resource('bahan-baku', RawMaterialController::class)
            ->except(['index', 'show'])->parameters(['bahan-baku' => 'id'])->names('materials');
    });

    // ── Supplier ───────────────────────────────────────────────────
    Route::get('/supplier', [SupplierController::class, 'index'])->middleware('can:supplier.lihat')->name('suppliers.index');
    Route::middleware('can:supplier.kelola')->group(function () {
        Route::resource('supplier', SupplierController::class)
            ->except(['index', 'show'])->parameters(['supplier' => 'id'])->names('suppliers');
    });

    // ── Pengawasan ─────────────────────────────────────────────────
    Route::middleware('can:pengguna.kelola')->group(function () {
        Route::resource('pengguna', UserController::class)
            ->except(['show', 'destroy'])->parameters(['pengguna' => 'id'])->names('users');
    });

    Route::get('/riwayat-aktivitas', AuditLogController::class)->middleware('can:riwayat-aktivitas.lihat')->name('audit.index');

    Route::middleware('can:data-terhapus.kelola')->group(function () {
        Route::get('/data-terhapus', [TrashController::class, 'index'])->name('trash.index');
        Route::patch('/data-terhapus/{type}/{id}', [TrashController::class, 'restore'])->whereNumber('id')->name('trash.restore');
    });
});
