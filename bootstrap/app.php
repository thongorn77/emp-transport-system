<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        // ยกเว้น CSRF สำหรับ driver routes
        // (LINE cache HTML เก่า ทำให้ CSRF token หมดอายุ — auth ใช้ LINE ID แทน)
        $middleware->validateCsrfTokens(except: [
            'driver/*',
            'register-driver',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
