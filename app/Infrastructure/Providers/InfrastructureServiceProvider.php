<?php

namespace App\Infrastructure\Providers;

use App\Application\Contracts\AuditLogger;
use App\Application\Contracts\Clock;
use App\Application\Contracts\RecycleBin;
use App\Application\Contracts\TransactionManager;
use App\Domain\Auth\UserRepository;
use App\Domain\Catalog\ProductRepository;
use App\Domain\Catalog\SupplierRepository;
use App\Domain\Inventory\RawMaterialRepository;
use App\Domain\Inventory\StockRepository;
use App\Domain\PreOrder\PreOrderRepository;
use App\Domain\Production\ProductionPlanRepository;
use App\Infrastructure\Persistence\Repositories;
use App\Infrastructure\Services;
use Illuminate\Support\ServiceProvider;

/**
 * Menyambungkan interface (Domain & Application) dengan implementasinya (Infrastructure).
 * Bila suatu saat ganti cara penyimpanan data, cukup ubah daftar di sini.
 */
class InfrastructureServiceProvider extends ServiceProvider
{
    public array $bindings = [
        PreOrderRepository::class => Repositories\EloquentPreOrderRepository::class,
        ProductionPlanRepository::class => Repositories\EloquentProductionPlanRepository::class,
        StockRepository::class => Repositories\EloquentStockRepository::class,
        RawMaterialRepository::class => Repositories\EloquentRawMaterialRepository::class,
        ProductRepository::class => Repositories\EloquentProductRepository::class,
        SupplierRepository::class => Repositories\EloquentSupplierRepository::class,
        UserRepository::class => Repositories\EloquentUserRepository::class,
        AuditLogger::class => Services\EloquentAuditLogger::class,
        TransactionManager::class => Services\DatabaseTransactionManager::class,
        Clock::class => Services\SystemClock::class,
        RecycleBin::class => Services\EloquentRecycleBin::class,
    ];
}
