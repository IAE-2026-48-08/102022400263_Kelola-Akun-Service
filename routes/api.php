<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Middleware\CheckIaeKey;

Route::prefix('v1')->middleware(CheckIaeKey::class)->group(function () {
    Route::get('/accounts', [AccountController::class, 'index']);
    Route::get('/accounts/{id}', [AccountController::class, 'show']);
    Route::get('/accounts/{id}/validation-status', [AccountController::class, 'validationStatus']);
    Route::post('/accounts/{id}/validate', [AccountController::class, 'validateAccount']);

    Route::post('/accounts/{id}/validate', [AccountController::class, 'validateAccount'])
        ->middleware('sso.federated');
});