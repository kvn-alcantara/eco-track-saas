<?php

use App\Http\Controllers\Api\CarbonReportController;
use App\Http\Controllers\Api\WasteRecordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware(['auth:sanctum', 'company.context'])
    ->group(function (): void {
        Route::get('/me', fn (Request $request) => $request->user()->load('company'));
        Route::apiResource('waste-records', WasteRecordController::class);
        Route::apiResource('carbon-reports', CarbonReportController::class);
    });

Route::middleware(['auth:sanctum', 'company.context'])
    ->post('/reports/generate', [CarbonReportController::class, 'generate']);
