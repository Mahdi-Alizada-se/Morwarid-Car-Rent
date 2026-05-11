<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isCustomer()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Access denied. Customer account required.',
                ], 403);
            }

            abort(403, 'Access denied. Customer account required.');
        }

        return $next($request);
    }
}