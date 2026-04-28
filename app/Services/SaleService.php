<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Events\Sale\SaleCreated;

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

    // REST API + BUSINESS FLOW
        
        // Expected JSON payload:
            // {
            //     "client_id": 1,
            //     "products": [
            //         {
            //          "product_id": 3,
            //          "quantity": 2,
            //         },
            //         {
            //          "product_id": 7,
            //          "quantity": 1,
            //         }
            //     ]
            // }
        // =====================

        // 1. Create a new sale
        // 2. Create n pivot register
        // 3. Update sale's total_amount
        // 4. Update clients' total_spent
        
    // ========================
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

            SaleCreated::dispatch($sale);

            return $sale->load('products', 'client');
        });
    }

    // REST API + BUSINESS RULES
        //
        // In this scenario, we defined the following business rule:
            //
            // A completed sale allows the user to:
            // - Replace one or more existing products
            // - Add one or more new products
            // - Remove one or more existing products
            //
        // =============================================================
            
        // Because this is still an update of the Sale resource,
            // we do not need a specific custom endpoint.
            //
            // We can use the standard update() method and handle the business
            // logic inside the service layer.
            //
            // This update process includes:
            // - Updating the pivot table (product_sale)
            // - Recalculating sales.total_amount
            // - Recalculating clients.total_spent
            //
            // This design keeps the API aligned with RESTful principles.
            //
        // =============================================================
        
        // Expected JSON payload:
            //
            // {
            //     "replace": [
            //         {
            //             "product_id": 5,
            //             "new_product_id": 2,
            //             "new_product_quantity": 2
            //         }
            //     ],
            //     "insert": [
            //         {
            //             "product_id": 4,
            //             "quantity": 1
            //         }
            //     ],
            //     "destroy": [
            //         {
            //             "product_id": 4
            //         }
            //     ]
            // }
            //
        // =============================================================

    // 1. Update pivot table records based on the received payload.
            //
            // replace:     Remove the old product and attach the new one
            //
            // insert:      Attach a new product to the current sale
            //
            // destroy:     Remove an existing product from the sale
    // ===========================================================

    public function update(Sale $sale, array $data)
    {
        return DB::transaction(function($item) use($sale,$data){

            // 1. Update the pivot table based on the received JSON.
            foreach ($data as $key => $value) {
                
                if( $key == "replace" && count($value) > 0 ){
                    foreach ($value as $replaceLine) {

                        // Remove old product relationship
                        $sale->products()->detach($replaceLine['product_id']);
                        
                        // Find the new product
                        $newProduct = Product::findOrFail($replaceLine['new_product_id']);
                        
                        // Attach the new product with updated pivot data
                        $sale->products()->attach($newProduct->id, [
                            'quantity' => $replaceLine['new_product_quantity'],
                            'amount' => $newProduct->price * $replaceLine['new_product_quantity'],
                        ]);
                    }
                }

                if( $key == "insert" && count($value) > 0 ){

                    foreach ($value as $insertLine) {

                        // Find the product to be inserted
                        $product = Product::findOrFail($insertLine['product_id']);
                        
                        // Attach the new product to the sale
                        $sale->products()->attach($product->id, [
                            'quantity' => $insertLine["quantity"],
                            'amount' => $product->price *  $insertLine["quantity"],
                        ]);

                    }

                }

                if( $key == "destroy" && count($value) > 0 ){

                    foreach ($value as $destroyLine) {

                        // Remove the product relationship from the sale
                        $sale->products()->detach($destroyLine['product_id']);
                    
                    }

                }
            }

            // 2. Recalculate sales.total_amount
            //
            // This uses pivot amounts already stored in product_sale

            $sale = $sale->recalculateTotal();

            // 3. Recalculate clients.total_spent
            //
            // This uses sales.total_amount instead of recalculating values directly from the pivot table
            
            $client = $sale->client->recalculateTotalSpent();
            
            // Return updated sale with fresh relationships
            return $sale->load('products', 'client');

        });
    }

    public function delete(Sale $sale)
    {
        $client = $sale->client;

        $sale->delete();

        $client->recalculateTotalSpent();

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