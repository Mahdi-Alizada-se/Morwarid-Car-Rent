<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastSeenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            // Only update every 60 seconds to avoid too many DB writes
            $user = auth()->user();
            if (
                !$user->last_seen_at ||
                $user->last_seen_at->lt(now()->subMinute())
            ) {
                $user->updateQuietly(['last_seen_at' => now()]);
            }
        }

        return $next($request);
    }
}