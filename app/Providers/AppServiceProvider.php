<?php

namespace App\Providers;

use App\Support\DeorisBroadcast;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $appUrl = (string) config('app.url');
        if (str_starts_with($appUrl, 'https://')) {
            URL::forceScheme('https');
        }

        // ── Broadcasting fallback ─────────────────────────────────────────────
        $connection = (string) config('broadcasting.default');

        if ($connection === 'reverb' && ! class_exists(\Laravel\Reverb\ReverbServiceProvider::class)) {
            config(['broadcasting.default' => 'log']);
        }

        if (in_array($connection, ['reverb', 'pusher'], true) && ! class_exists(\Pusher\Pusher::class)) {
            config(['broadcasting.default' => 'log']);
        }

        if (! DeorisBroadcast::isEnabled() && in_array(config('broadcasting.default'), ['reverb', 'pusher'], true)) {
            config(['broadcasting.default' => 'log']);
        }

        // ── Rate limiters ─────────────────────────────────────────────────────

        // v1 API: 120 req/min per authenticated user (or IP as fallback)
        RateLimiter::for('api', function (Request $request) {
            $key = $request->session()->get('sso_id') ?? $request->ip();
            return Limit::perMinute(120)->by($key);
        });

        // SSO exchange: 10 req/min per IP (anti-brute-force)
        RateLimiter::for('sso', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });
    }

    private function repinEnvFromFile(): void
    {
        $envFile = base_path('.env');
        if (! is_readable($envFile)) { return; }
        $pin = ['APP_KEY', 'APP_ENV', 'SESSION_DRIVER', 'SESSION_COOKIE',
                'SESSION_DOMAIN', 'SESSION_SECURE_COOKIE', 'SESSION_SAME_SITE',
                'BROADCAST_CONNECTION', 'DB_CONNECTION', 'DB_DATABASE'];
        $map = [
            'APP_KEY'               => 'app.key',
            'APP_ENV'               => 'app.env',
            'SESSION_DRIVER'        => 'session.driver',
            'SESSION_COOKIE'        => 'session.cookie',
            'SESSION_SAME_SITE'     => 'session.same_site',
            'SESSION_SECURE_COOKIE' => 'session.secure',
            'BROADCAST_CONNECTION'  => 'broadcasting.default',
            'DB_DATABASE'           => 'database.connections.mysql.database',
        ];
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if ($line === '' || $line[0] === '#') { continue; }
            $eq = strpos($line, '=');
            if ($eq === false) { continue; }
            $key = trim(substr($line, 0, $eq));
            if (! in_array($key, $pin, true)) { continue; }
            $val = trim(substr($line, $eq + 1));
            if (strlen($val) >= 2 && $val[0] === '"' && $val[-1] === '"') { $val = substr($val, 1, -1); }
            elseif (strlen($val) >= 2 && $val[0] === "'" && $val[-1] === "'") { $val = substr($val, 1, -1); }
            $_SERVER[$key] = $val;
            if (isset($map[$key])) { config([$map[$key] => $val]); }
        }
    }
}
