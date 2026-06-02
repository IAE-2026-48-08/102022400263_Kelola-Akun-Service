<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIaeKey
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-IAE-KEY');
        
        if (!$apiKey || $apiKey !== '102022400263') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Missing or invalid X-IAE-KEY header',
                'data' => null,
                'meta' => null
            ], 401);
        }

        return $next($request);
    }
}