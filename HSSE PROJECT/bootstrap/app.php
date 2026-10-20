<?php

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
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'role' => \App\Http\Middleware\EnsureRole::class,
            'can.manage' => \App\Http\Middleware\EnsureCanManageModule::class,
            'can.export' => \App\Http\Middleware\EnsureCanExportPdf::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, \Illuminate\Http\Request $request) {
            $message = 'Ukuran berkas yang diunggah melebihi batas kapasitas maksimal server (post_max_size). Silakan pilih berkas yang lebih kecil (maksimal 5 MB per berkas).';

            if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 413);
            }

            return back()->withInput()->with('error', $message);
        });
    })->create();
