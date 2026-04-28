<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Client\ClientController;
use App\Http\Controllers\Api\V1\Supplier\SupplierController;
use App\Http\Controllers\Api\V1\Product\ProductController;
use App\Http\Controllers\Api\V1\Sale\SaleController;

Route::prefix('v1')->group(function () {

    Route::get('/health', HealthController::class);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::apiResource('clients', ClientController::class);
        // Route::get('/clients/{client}', [ClientController::class, 'show']);
        Route::apiResource('suppliers', SupplierController::class);
        Route::apiResource('products', ProductController::class);
        Route::apiResource('sales', SaleController::class);
    });

});

// postman --disable-gpu
// rm -rf ~/snap/postman/common/.config/Postman/Cache/*
// rm -rf ~/snap/postman/common/.config/Postman/GPUCache/*
// rm -rf ~/snap/postman/common/.config/Postman/Partitions/*

// GET    /clients            -> index
// POST   /clients            -> store
// GET    /clients/{client}   -> show
// PUT    /clients/{client}   -> update
// PATCH  /clients/{client}   -> update
// DELETE /clients/{client}   -> destroy

// resource() inclui: create() e edit()
// usado mais para aplicações Blade / MVC clássico.

// apiResource() não inclui: create() edit()
// ideal para APIs REST.

// php artisan optimize:clear
// php artisan config:clear
// php artisan cache:clear
// php artisan route:clear
// php artisan view:clear