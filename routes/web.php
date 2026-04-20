<?php

use Illuminate\Support\Facades\Route;
use App\Http\Resources\Api\V1\Sale\SaleResource;
use App\Services\SaleService;

Route::get('/', function () {
    return view('welcome');
});

// SEE README → D.S_1 

    /*
    |--------------------------------------------------------------------------
    | Display data + Debugbar outcomes
    |--------------------------------------------------------------------------
    |
    | Uncomment below to test using Laravel Debugbar.
    |
    */

    Route::get('/debug-sales-lazy', function () {
        $sales = app(SaleService::class)->listLazy();
        return view('debug.sales', compact('sales'));
    });

    Route::get('/debug-sales-eager', function () {
        $sales = app(SaleService::class)->listEager();
        return view('debug.sales', compact('sales'));
    });
// ==================