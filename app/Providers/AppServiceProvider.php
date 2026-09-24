<?php

namespace App\Providers;

use App\Domain\Auth\Permission;
use App\Infrastructure\Persistence\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use App\Domain\PreOrder\PreOrderStatus;
use App\Infrastructure\Persistence\Models\PreOrder;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Setiap Permission menjadi Gate, sehingga bisa dipakai di route (middleware "can:...")
        // dan di tampilan (@can('manage-transactions')).
        foreach (Permission::cases() as $permission) {
            Gate::define($permission->value, fn (User $user) => $user->hasPermission($permission));
        }

        Route::resourceVerbs(['create' => 'baru', 'edit' => 'ubah']);
        Paginator::useBootstrapFive();
        Carbon::setLocale('id');

        // Angka kecil di menu samping: pre-order yang lewat jatuh tempo.
        View::composer('layouts.app', function ($view) {
            $view->with('navCounts', [
                'overdue' => PreOrder::whereIn('status', array_map(fn ($s) => $s->value, PreOrderStatus::active()))
                    ->where('due_date', '<', today()->toDateString())
                    ->count(),
            ]);
        });
    }
}
