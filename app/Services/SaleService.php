<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function list()
    {
        // SEE README → D.S_1 

            /*
            |--------------------------------------------------------------------------
            | N + 1 query problem
            |--------------------------------------------------------------------------
            |
            | Uncomment below to test using Laravel Debugbar.
            |
            */

            // return Sale::latest()->paginate(10);

            /*
            |--------------------------------------------------------------------------
            | Optimized Solution using Eager Loading
            |--------------------------------------------------------------------------
            |
            | Result:
            | only 2 queries total
            |
            */

        // ==================

        return Sale::with([
            'client','products'
        ])
        ->latest()
        ->paginate(10);
    }

    //Metodo attach
    // 1. attach cria registro na pivot
    // 2. sale_id vem automaticamente do model que iniciou a chamada
    // 3. parâmetro 1 = id do model relacionado (product_id)
    // 4. parâmetro 2 = campos extras da pivot

    public function create(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            $sale = Sale::create([
                'client_id' => $data['client_id'],
                'total_amount' => 0,
            ]);

            $totalAmount = 0;

            foreach ($data['products'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $amount = $item['quantity'] * $product->price;

                $sale->products()->attach($item['product_id'], 
                [
                    'quantity' => $item['quantity'],
                    'amount' => $amount,
                ]);
                $totalAmount += $amount;
            }

            $sale->update([
                'total_amount' => $totalAmount,
            ]);

            $sale->client()->increment('total_spent', $totalAmount);

            return $sale->load('products', 'client');
        });
    }

    



// SEE README → D.S_1 

    /*
    |--------------------------------------------------------------------------
    | Display data + Debugbar outcomes
    |--------------------------------------------------------------------------
    |
    | Uncomment below to test using Laravel Debugbar.
    |
    */

    // public function listLazy()
    // {
    //     return Sale::latest()->paginate(10);
    // }

    // public function listEager()
    // {
    //     return Sale::with('client')
    //         ->latest()
    //         ->paginate(10);
    // }

// ==================

}