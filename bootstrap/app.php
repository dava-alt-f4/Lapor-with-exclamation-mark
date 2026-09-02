<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'admin' => AdminMiddleware::class,
        ]);

        $middleware->redirectUsersTo(function (Request $request) {
            return Auth::check() && Auth::user()->role === 'admin'
                ? route('admin.dashboard', absolute: false)
                : route('dashboard', absolute: false);
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle error 429 with toast
        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if (! $request->expectsJson() && ! app()->environment('testing')) {
                Inertia::flash('toast', [
                    'type' => 'error',
                    'message' => 'Too many requests. Please try again later.',
                ]);

                return back();
            }
        });
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Handle custom error
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            $validStatuses = [403, 404, 500, 503, 419];
            $status = $response->getStatusCode();

            if (! app()->environment('testing') && ! $request->is('api/*') && in_array($status, $validStatuses)) {
                return Inertia::render('Error', [
                    'status' => $status,
                ])->toResponse($request)->setStatusCode($status);
            }

            return $response;
        });
    })->create();
