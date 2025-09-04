<?php

use App\Http\Controllers\HealthCheckController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Health Check API Routes
Route::prefix('health')->group(function () {
    Route::get('/', [HealthCheckController::class, 'health']);
    Route::get('/status', [HealthCheckController::class, 'status']);
    Route::get('/metrics', [HealthCheckController::class, 'metrics']);
    Route::get('/service/{serviceName}', [HealthCheckController::class, 'service']);
});
