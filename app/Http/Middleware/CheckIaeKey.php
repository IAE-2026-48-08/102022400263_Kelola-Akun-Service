<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIaeKey
{
    /**
     * Handle an incoming request.
     * Memvalidasi header X-IAE-KEY sesuai Standard Integration Contract (IAE-T2).
     * Nilai key yang valid adalah NIM mahasiswa: 102022400263
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = trim((string) $request->header('X-IAE-KEY', ''));

        // Tolak jika header tidak dikirim sama sekali
        if ($apiKey === '') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized: Header X-IAE-KEY wajib disertakan',
                'errors'  => null,
            ], 401);
        }

        // Tolak jika key salah / tidak cocok dengan NIM mahasiswa
        if ($apiKey !== '102022400263') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized: Nilai X-IAE-KEY tidak valid',
                'errors'  => null,
            ], 401);
        }

        return $next($request);
    }
}