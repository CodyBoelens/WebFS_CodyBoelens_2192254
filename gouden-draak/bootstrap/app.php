<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Inertia middleware voor SPA-gedrag
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\SetLocale::class,
        ]);

        // Alias voor rol-gebaseerde toegangscontrole
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        // UC-19: Elke dag om 01:00 de rapportage van gisteren genereren
        $schedule->command('rapportage:dagelijks')->dailyAt('01:00');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
