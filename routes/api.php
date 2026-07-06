<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\CarbonReportController;
use App\Http\Controllers\Api\WasteRecordController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'company.context'])->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', fn (Request $request) => new UserResource($request->user()->load('company')));
        Route::apiResource('waste-records', WasteRecordController::class);
        Route::apiResource('carbon-reports', CarbonReportController::class);
        Route::post('/carbon-reports/{carbon_report}/approve', [CarbonReportController::class, 'approve']);
        Route::post('/reports/generate', [CarbonReportController::class, 'generate']);
        Route::apiResource('audit-logs', AuditLogController::class)->only(['index', 'show']);
    });
});

