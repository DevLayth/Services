<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $providedKey = $request->header('X-API-KEY');
        $validKey = config('app.api_key');

        if (!$providedKey || $providedKey !== $validKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or missing API key.',
                'status_code' => 401
            ], 401);
        }

        return $next($request);
    }
}
