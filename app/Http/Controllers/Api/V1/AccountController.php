<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    description: "Dokumentasi API untuk Service 1 - Kelola Akun (Tugas 2 IAE)",
    title: "Account Service API"
)]
#[OA\Server(
    url: "http://127.0.0.1:8000",
    description: "Local Development Server"
)]
#[OA\SecurityScheme(
    securityScheme: "ApiKeyAuth",
    type: "apiKey",
    in: "header",
    name: "X-IAE-KEY",
    description: "Masukkan NIM kamu sebagai API Key (contoh: 102022400263)"
)]
class AccountController extends Controller
{
    private $dummyAccounts = [
        1 => [
            'id' => 1,
            'nama' => 'Nugraha Ade',
            'email' => 'nugraha@example.com',
            'saldo' => 5000000,
            'status_validasi' => 'verified'
        ],
        2 => [
            'id' => 2,
            'nama' => 'Hebat Kali nampak awak yakannnn',
            'email' => 'bilek@example.com',
            'saldo' => 7500000,
            'status_validasi' => 'pending'
        ]
    ];

    private function successResponse($data, $message = 'Data retrieved successfully', $code = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'meta' => [
                'service_name' => 'Account-Service',
                'api_version' => 'v1'
            ]
        ], $code);
    }

    private function errorResponse($message = 'Resource not found', $code = 404, $errors = null)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors
        ], $code);
    }

    #[OA\Get(
        path: "/api/v1/accounts",
        summary: "Ambil daftar semua akun nasabah",
        tags: ["Accounts"],
        security: [["ApiKeyAuth" => []]]
    )]
    #[OA\Response(response: 200, description: "Berhasil mengambil data")]
    public function index()
    {
        return $this->successResponse(array_values($this->dummyAccounts), 'Daftar semua akun nasabah berhasil diambil');
    }

    #[OA\Get(
        path: "/api/v1/accounts/{id}",
        summary: "Ambil detail & saldo akun tertentu",
        tags: ["Accounts"],
        security: [["ApiKeyAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Berhasil mengambil detail akun")]
    #[OA\Response(response: 404, description: "Akun tidak ditemukan")]
    public function show($id)
    {
        if (!array_key_exists($id, $this->dummyAccounts)) {
            return $this->errorResponse('Account not found', 404);
        }
        return $this->successResponse($this->dummyAccounts[$id], 'Detail akun berhasil diambil');
    }

    #[OA\Get(
        path: "/api/v1/accounts/{id}/validation-status",
        summary: "Cek hasil validasi akun nasabah",
        tags: ["Accounts"],
        security: [["ApiKeyAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Berhasil mengecek status validasi")]
    #[OA\Response(response: 404, description: "Akun tidak ditemukan")]
    public function validationStatus($id)
    {
        if (!array_key_exists($id, $this->dummyAccounts)) {
            return $this->errorResponse('Account not found', 404);
        }
        $data = [
            'id' => $this->dummyAccounts[$id]['id'],
            'status_validasi' => $this->dummyAccounts[$id]['status_validasi']
        ];
        return $this->successResponse($data, 'Status validasi akun berhasil diambil');
    }

    #[OA\Post(
        path: "/api/v1/accounts/{id}/validate",
        summary: "Memvalidasi kelayakan akun nasabah",
        tags: ["Accounts"],
        security: [["ApiKeyAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Akun berhasil divalidasi")]
    #[OA\Response(response: 404, description: "Akun tidak ditemukan")]
    public function validateAccount(Request $request, $id)
    {
        if (!array_key_exists($id, $this->dummyAccounts)) {
            return $this->errorResponse('Account not found', 404);
        }
        $data = [
            'id' => (int) $id,
            'status_validasi' => 'verified'
        ];
        return $this->successResponse($data, 'Account validation processed successfully', 200);
    }
}