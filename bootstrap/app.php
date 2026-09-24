<?php

use App\Domain\Shared\BusinessRuleException;
use App\Domain\Shared\NotFoundException;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [EnsureUserIsActive::class]);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Pelanggaran aturan bisnis adalah hal wajar, tidak perlu dicatat sebagai error di log.
        $exceptions->dontReport(BusinessRuleException::class);

        // Pelanggaran aturan bisnis (dari Domain) ditampilkan sebagai pesan, bukan halaman error.
        $exceptions->render(function (BusinessRuleException $e, Request $request) {
            if ($e instanceof NotFoundException && $request->isMethod('GET')) {
                abort(404, $e->getMessage());
            }

            return back()->withInput()->with('error', $e->getMessage());
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
