<?php

use App\Http\Middleware\RedirectBasedOnRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Registrar el middleware de redirección basado en roles
        $middleware->web(append: [
            RedirectBasedOnRole::class,
        ]);

        // Registrar como alias para poder usarlo en rutas específicas
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckUserRole::class,
            'redirect.role' => \App\Http\Middleware\RedirectBasedOnRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
