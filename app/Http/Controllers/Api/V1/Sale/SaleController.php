<?php

namespace App\Http\Controllers\Api\V1\Sale;

use App\Http\Controllers\Controller;
use App\Services\SaleService;
use App\Models\Sale;
use App\Models\Product;
use App\Http\Resources\Api\V1\Sale\SaleResource;
use App\Http\Requests\Api\V1\Sale\StoreSaleRequest;
use App\Http\Requests\Api\V1\Sale\UpdateSaleRequest;
use Illuminate\Support\Facades\Mail;
use App\Mail\Sale\SaleConfirmationMail;

class SaleController extends Controller
{
    public function __construct(private SaleService $service) {}

    public function index()
    {
        return SaleResource::collection(
            $this->service->list()
        );
    }

// SEE README → D.S_4 
    /*
    |--------------------------------------------------------------------------
    | Synchronous Execution (NOT RECOMMENDED)
    |--------------------------------------------------------------------------
    |
    | Uncomment below to test using Laravel logs.
    |
    */    

    // public function store(StoreSaleRequest $request)
    // {
    //     $data = $request->validated();
    //     $sale = Sale::create([
    //             'client_id' => $data['client_id'],
    //             'total_amount' => 0,
    //         ]);
    //     $totalAmount = 0;

    //     foreach ($data['products'] as $item) {
    //         $product = Product::findOrFail($item['product_id']);
    //         $amount = $item['quantity'] * $product->price;

    //         $sale->products()->attach($item['product_id'], 
    //         [
    //             'quantity' => $item['quantity'],
    //             'amount' => $amount,
    //         ]);
    //         $totalAmount += $amount;
    //     }

    //     $sale->update([
    //         'total_amount' => $totalAmount,
    //     ]);

    //     $sale->client()->increment('total_spent', $totalAmount);

    //     Mail::to($sale->client->email)->send(new SaleConfirmationMail($sale));

    //     return new SaleResource($sale->load('products', 'client'));
        
    // }
    
     /*
    |--------------------------------------------------------------------------
    | Problems
    |--------------------------------------------------------------------------
    |
    | - Blocks HTTP response until email is sent (slow API response)
    | - User waits for SMTP processing
    | - Couples business logic with external service (SMTP)
    | - Poor scalability under high traffic
    | - No retry strategy if mail fails during request
    |
    |--------------------------------------------------------------------------
    */



    /*
    |--------------------------------------------------------------------------
    | Asynchronous Execution (RECOMMENDED)
    |--------------------------------------------------------------------------
    |
    | Fully decoupled execution using Laravel Jobs & Queues.
    |
    */

    public function store(StoreSaleRequest $request)
    {
        $sale = $this->service->create($request->validated());

        return new SaleResource($sale);
    }
    
// ==================

    public function show(Sale $sale)
    {
        return new SaleResource($sale);
    }

    public function update(UpdateSaleRequest $request, Sale $sale)
    {
        $sale = $this->service->update($sale, $request->validated());
        // return $sale;
        return new SaleResource($sale);
    }

    public function destroy(Sale $sale)
    {
        $this->service->delete($sale);

        return response()->json(['message' => 'Deleted']);
    }

    
}
