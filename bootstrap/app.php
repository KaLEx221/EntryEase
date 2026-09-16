<?php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Render (and similar TLS terminators) forward X-Forwarded-Proto. Without
        // this, Laravel sees the origin request as http and asset()/url()/route()
        // emit http:// URLs, which DEORIS CSP frame-src rejects.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'entryease.role' => \App\Http\Middleware\EnsureEntryEaseRole::class,
            'portal.search'  => \App\Http\Middleware\AuthenticatePortalSearchToken::class,
            'sso.required'   => \App\Http\Middleware\EnsureSsoAuthenticated::class,
        ]);

        // Apply SSO middleware to web routes AFTER session middleware initializes
        $middleware->web(append: [
            \App\Http\Middleware\EnsureSsoAuthenticated::class,
            \App\Http\Middleware\TrackNavigationHistory::class,
        ]);

        // API routes need session for SSO session-based auth
        $middleware->api(append: [
            \Illuminate\Session\Middleware\StartSession::class,
        ]);

        // Pin session cookie config before session starts — prevents XAMPP
        // cross-vhost contamination from bleeding another module's cookie name/SameSite.
        $middleware->prependToGroup('web', \App\Http\Middleware\ForceSessionCookies::class);
        $middleware->prependToGroup('api', \App\Http\Middleware\ForceSessionCookies::class);

        $middleware->validateCsrfTokens(except: [
            'entryease/api/events/inbound',
            'api/sso/*',    // SSO endpoints don't use CSRF tokens
            'api/v1/*',     // v1 API uses session auth, not CSRF
            'sso/redirect', // Legacy SSO redirect endpoint
            'sso/exchange', // Token exchange — CSRF not applicable (token-based)
        ]);

        // Content Security Policy — allows portal to embed this module in an iframe
        $middleware->append(\App\Http\Middleware\ModuleCspMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error'   => 'unauthenticated',
                    'message' => 'SSO authentication required.',
                ], 401);
            }
        });

        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error'   => 'forbidden',
                    'message' => $e->getMessage() ?: 'You do not have permission to perform this action.',
                ], 403);
            }
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error'   => 'validation_failed',
                    'message' => 'The given data was invalid.',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error'   => 'not_found',
                    'message' => 'The requested resource was not found.',
                ], 404);
            }
        });

        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error'   => 'too_many_requests',
                    'message' => 'Too many requests. Please slow down.',
                ], 429);
            }
        });
    })->create();
