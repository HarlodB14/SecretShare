<?php

namespace App\Http\Middleware;

use App\Models\Secret;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CleanupExpiredSecretsMiddleware
{

    public function handle(Request $request, Closure $next): Response
    {
        $deleted = Secret::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->delete();

        if ($deleted > 0) {
            Log::info("Middleware cleaned up {$deleted} expired secret(s)");
        }

        return $next($request);
    }
}
