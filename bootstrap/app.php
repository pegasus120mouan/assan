<?php

use App\Exceptions\InsufficientStockException;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\EnsureUserIsStaff;
use App\Support\AuthRedirect;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware(['web', 'auth', 'active', 'staff'])
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'active' => EnsureUserIsActive::class,
            'staff' => EnsureUserIsStaff::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => AuthRedirect::homeUrl(auth()->user()));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (InsufficientStockException $exception, Request $request) {
            $message = 'Stock insuffisant. Disponible : '.$exception->available.'.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->withInput()->withErrors(['quantity' => $message]);
        });
    })->create();
