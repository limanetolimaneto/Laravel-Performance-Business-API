<?php

use Illuminate\Support\Facades\Route;
use App\Http\Resources\Api\V1\Sale\SaleResource;
use App\Services\SaleService;

Route::get('/debug-sales-lazy', function () {
    $sales = app(SaleService::class)->listLazy();
    return view('debug.sales', compact('sales'));
});

Route::get('/debug-sales-eager', function () {
    $sales = app(SaleService::class)->listEager();
    return view('debug.sales', compact('sales'));
});


Route::get('/', function () {
    return view('welcome');
});


// Route::get('/debug-sales-lazy', function () {
//     return SaleResource::collection(
//         app(SaleService::class)->listLazy()
//     );
// });

// Route::get('/debug-sales-eager', function () {
//     return SaleResource::collection(
//         app(SaleService::class)->listEager()
//     );
// });