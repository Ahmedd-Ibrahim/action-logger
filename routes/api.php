<?php

use Illuminate\Support\Facades\Route;
use BIM\ActionLogger\Http\Controllers\ActionLogController;
use BIM\ActionLogger\Http\Controllers\ModelActivityController;
use BIM\ActionLogger\Http\Controllers\CauserActivityController;

Route::prefix('action-logs')->group(function () {
    // Main CRUD routes
    Route::get('/', [ActionLogController::class, 'index']);
    Route::get('/{batchUuid}', [ActionLogController::class, 'show']);

    // Model activities routes
    Route::prefix('models')->group(function () {
        Route::get('/{modelType}/{modelId}', [ModelActivityController::class, 'index']);
    });

    // Causer activities routes
    Route::prefix('causers')->group(function () {
        Route::get('/{causerType}/{causerId}', [CauserActivityController::class, 'index']);
    });
}); 