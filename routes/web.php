<?php

use App\Http\Controllers\HealthCheckController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Health Check Routes
Route::prefix('health')->group(function () {
    Route::get('/', [HealthCheckController::class, 'health'])->name('health.check');
    Route::get('/status', [HealthCheckController::class, 'status'])->name('health.status');
    Route::get('/metrics', [HealthCheckController::class, 'metrics'])->name('health.metrics');
    Route::get('/service/{serviceName}', [HealthCheckController::class, 'service'])->name('health.service');
});

// Test Service Routes (for simulating monitored endpoints)
Route::prefix('api')->group(function () {
    Route::get('/status', [App\Http\Controllers\TestServiceController::class, 'status']);
    Route::get('/unreliable', [App\Http\Controllers\TestServiceController::class, 'unreliable']);
    Route::get('/slow', [App\Http\Controllers\TestServiceController::class, 'slow']);
});
