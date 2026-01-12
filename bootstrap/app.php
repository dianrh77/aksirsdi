<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Application;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (HttpException $e, $request) {
            if ($e->getStatusCode() === 419) {
                // ✅ LOG: memastikan handler ini benar-benar kepanggil
                // Log::warning('419 intercepted (CSRF/session expired)', [
                //     'url' => $request->fullUrl(),
                //     'ip' => $request->ip(),
                //     'user_id' => optional($request->user())->id,
                //     'ua' => $request->userAgent(),
                // ]);
                
                Alert::error('Warning', 'Sesi telah habis, silakan login kembali');
                return redirect()
                    ->route('login');
            }
        });
    })->create();
