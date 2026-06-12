<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;

class VerifyFederatedSso
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Ekstrak Token dari Header Authorization
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->unauthorizedResponse('Akses ditolak: Token JWT tidak ditemukan atau format Bearer salah.');
        }

        $jwt = $matches[1];

        try {
            // 2. Ambil JWKS (Public Keys) dari Cloud Pak Ekky dengan Cache 1 jam
            $jwks = Cache::remember('sso_jwks', 3600, function () {
                $ssoUrl = env('SSO_BASE_URL', 'https://iae-sso.virtualfri.id');
                $response = Http::get($ssoUrl . '/api/v1/auth/jwks');
                
                if ($response->failed()) {
                    throw new Exception('Gagal mengambil Public Key dari Cloud SSO Dosen.');
                }
                
                return $response->json();
            });

            // Parse JWKS menjadi format key yang dimengerti library firebase
            $keys = JWK::parseKeySet($jwks);

            // 3. Dekode dan Verifikasi Signature JWT menggunakan RS256 terpusat
            $decoded = JWT::decode($jwt, $keys);

            // 4. Ambil informasi user/role secara fleksibel dari payload token
            // Jika object 'role' atau 'roles' tidak ditemukan, diset otomatis ke 'warga' agar tidak memicu Undefined Property
            $userRole = 'warga';
            if (isset($decoded->role)) {
                $userRole = $decoded->role;
            } elseif (isset($decoded->roles)) {
                $userRole = is_array($decoded->roles) ? $decoded->roles[0] : $decoded->roles;
            }

            // 5. Masukkan data payload utuh dan role hasil mapping ke dalam request attributes
            $request->attributes->add([
                'sso_payload' => $decoded,
                'sso_role' => $userRole
            ]);

            // 6. Loloskan request secara mutlak karena signature token RS256 terbukti asli dari Cloud Dosen
            return $next($request);

        } catch (ExpiredException $e) {
            return $this->unauthorizedResponse('Akses ditolak: Token JWT sudah kedaluwarsa.');
        } catch (SignatureInvalidException $e) {
            return $this->unauthorizedResponse('Akses ditolak: Signature JWT tidak valid (Verifikasi RS256 Gagal).');
        } catch (Exception $e) {
            return $this->unauthorizedResponse('Akses ditolak: Gagal memproses otorisasi. ' . $e->getMessage());
        }
    }

    /**
     * Format response error standar IAE (401 Unauthorized)
     */
    private function unauthorizedResponse(string $message): Response
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => null,
            'meta' => null
        ], 401);
    }
}