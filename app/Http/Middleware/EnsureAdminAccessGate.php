<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccessGate
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. If already authenticated as an admin user, allow access
        if ($request->user()?->isAdmin()) {
            return $next($request);
        }

        $configuredKey = (string) config('admin.access_key', '');

        // 2. If a secret key is provided in the query string (?key=... or ?access_key=...)
        $providedKey = (string) ($request->query('key') ?? $request->query('access_key') ?? '');

        if ($configuredKey !== '' && $providedKey !== '' && hash_equals($configuredKey, $providedKey)) {
            // Unlock the admin portal gate for this session
            session(['admin_gate_unlocked' => true]);

            return $next($request);
        }

        // 3. If the session has already unlocked the gate, allow access to the login flow
        if (session('admin_gate_unlocked') === true) {
            return $next($request);
        }

        // 4. If no access key is configured at all (disabled), proceed
        if ($configuredKey === '') {
            return $next($request);
        }

        // 5. Hide the admin area completely: pretend the page does not exist
        abort(404);
    }
}
