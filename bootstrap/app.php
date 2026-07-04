<?php

use App\Http\Middleware\GenerateRequestId;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\Locale\SetLocale;
use App\Http\Middleware\Locale\Sync\AppSidebar as SyncAppSidebarLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->alias([
            'locale.sync.appsidebar' => SyncAppSidebarLocale::class,
        ]);

        // Global, not scoped to the 'web' group: must run even when a
        // request matches no route at all (a plain 404).
        $middleware->append(GenerateRequestId::class);

        $middleware->web(append: [
            SetLocale::class,
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            $status = $response->getStatusCode();

            // 419 (CSRF mismatch) is almost always a stale form
            // submission — sending the user back with a flashed
            // message preserves their place, instead of a dead-end
            // full error page that loses whatever they were doing.
            if ($status === 419) {
                Inertia::flash('toast', [
                    'type' => 'error',
                    'message' => __('errors.419.message'),
                ]);

                return back();
            }

            if (! app()->environment(['local', 'testing']) && in_array($status, [403, 404, 429, 500, 503], true)) {
                syncLangFiles(['pages/errors']);

                return Inertia::render('error', [
                    'status' => $status,
                    // Passed explicitly rather than relied on via
                    // Inertia's shared props: for an unmatched route,
                    // HandleInertiaRequests never runs, so 'requestId'
                    // is never pushed into shared data.
                    'requestId' => $request->attributes->get('request_id'),
                ])
                    ->toResponse($request)
                    ->setStatusCode($status);
            }

            return $response;
        });
    })->create();
