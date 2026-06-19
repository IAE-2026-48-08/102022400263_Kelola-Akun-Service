<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

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
        summary: "Memvalidasi kelayakan akun nasabah, mencatat Audit SOAP, dan Broadcast RabbitMQ",
        tags: ["Accounts"],
        security: [["ApiKeyAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Akun berhasil divalidasi, diaudit, dan disebarkan ke broker pesan")]
    #[OA\Response(response: 404, description: "Akun tidak ditemukan")]
    #[OA\Response(response: 500, description: "Gagal memproses orkestrasi ke server pusat")]
    public function validateAccount(Request $request, $id)
    {
        // 1. Cek ketersediaan data akun
        if (!array_key_exists($id, $this->dummyAccounts)) {
            return $this->errorResponse('Account not found', 404);
        }

        try {
            // ====================================================================
            // FASE 1: REQUEST M2M TOKEN KE SERVER SSO (LOGIN SEBAGAI SERVICE)
            // ====================================================================
            $ssoResponse = Http::post('https://iae-sso.virtualfri.id/api/v1/auth/token', [
                'api_key' => 'KEY-MHS-274',
                'nim'     => '102022400263'
            ]);

            if ($ssoResponse->failed()) {
                return $this->errorResponse('Gagal mendapatkan M2M Token dari SSO Dosen', 500);
            }

            $m2mToken = $ssoResponse->json('token') ?? $ssoResponse->json('access_token');

            // ====================================================================
            // FASE 2: RAKIT DATA & KIRIM KE SERVER SOAP MENGGUNAKAN M2M TOKEN
            // ====================================================================
            $logData = json_encode([
                'account_id' => (int) $id,
                'action' => 'Validation',
                'status' => 'verified',
                'timestamp' => now()->toIso8601String()
            ]);

            $xmlBody = '<?xml version="1.0" encoding="UTF-8"?>
            <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:iae="http://iae.central/audit">
                <soap:Body>
                    <iae:AuditRequest>
                        <iae:TeamID>TEAM04</iae:TeamID>
                        <iae:ActivityName>AccountValidated</iae:ActivityName>
                        <iae:LogContent><![CDATA[' . $logData . ']]></iae:LogContent>
                    </iae:AuditRequest>
                </soap:Body>
            </soap:Envelope>';

            $soapResponse = Http::withToken($m2mToken)
                ->withHeaders([
                    'Content-Type' => 'text/xml; charset=UTF-8'
                ])
                ->send('POST', 'https://iae-sso.virtualfri.id/soap/v1/audit', [
                    'body' => $xmlBody
                ]);
            
            // ====================================================================
            // FASE 3: PARSING BALASAN (RECEIPT NUMBER) VIA XPATH
            // ====================================================================
            $xml = simplexml_load_string($soapResponse->body());
            
            if ($xml === false) {
                return $this->errorResponse('Gagal membaca response XML dari server pusat', 500);
            }

            $xml->registerXPathNamespace('iae', 'http://iae.central/audit');
            $receiptElements = $xml->xpath('//iae:ReceiptNumber');

            if (empty($receiptElements)) {
                return $this->errorResponse('ReceiptNumber tidak ditemukan pada response SOAP', 500);
            }

            $receiptNumber = (string) $receiptElements[0];

            // ====================================================================
            // FASE 4: BROADCAST EVENT VIA HTTP BRIDGE (RABBITMQ PUZZLE FINAL)
            // ====================================================================
            // Menyusun payload dengan standar arsitektur enterprise yang padat & estetik
            $eventPayload = [
                'event_name'   => 'account.validated',
                'service_name' => 'Account-Service',
                'api_version'  => 'v1',
                'team_id'      => 'TEAM04',
                'timestamp'    => now()->toIso8601String(),
                'payload'      => [
                    'account_id'      => (int) $id,
                    'status_validasi' => 'verified',
                    'receipt_no'      => $receiptNumber
                ]
            ];

            // Menembak titipan pesan JSON ke asisten HTTP Bridge menggunakan M2M token
            $rabbitResponse = Http::withToken($m2mToken)
                ->post('https://iae-sso.virtualfri.id/api/v1/messages/publish', $eventPayload);

            if ($rabbitResponse->failed()) {
                return $this->errorResponse('Gagal mengirim broadcast message ke RabbitMQ Bridge', 500);
            }

            // ====================================================================
            // FASE 5: STATE-CHANGING DATABASE LOKAL & RESPON SUKSES
            // ====================================================================
            /*
            DB::table('accounts')
                ->where('id', $id)
                ->update([
                    'status_validasi' => 'verified',
                    'receipt_no' => $receiptNumber
                ]);
            */

            $data = [
                'id' => (int) $id,
                'status_validasi' => 'verified',
                'receipt_no' => $receiptNumber,
                'broadcast_status' => 'success'
            ];

            return $this->successResponse($data, 'Account validation processed, audited, and broadcasted successfully', 200);

        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan pada proses integrasi: ' . $e->getMessage(), 500);
        }
    }
}