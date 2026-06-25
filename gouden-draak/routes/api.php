<?php

use App\Http\Controllers\Api\HelpRequestController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Producten opzoeken (US-9: kassa zoekfunctie)
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);

    // Bestellingen (US-1 tablet, kassa)
    Route::apiResource('orders', OrderController::class)->except(['destroy']);

    // Hulpverzoeken (US-5)
    Route::post('/help-requests', [HelpRequestController::class, 'store']);
    Route::get('/help-requests', [HelpRequestController::class, 'index'])->middleware('auth:sanctum');
    Route::put('/help-requests/{helpRequest}/dismiss', [HelpRequestController::class, 'dismiss'])->middleware('auth:sanctum');
});
