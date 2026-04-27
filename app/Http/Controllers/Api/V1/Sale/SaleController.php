<?php

namespace App\Http\Controllers\Api\V1\Sale;

use App\Http\Controllers\Controller;
use App\Services\SaleService;
use App\Models\Sale;
use App\Http\Resources\Api\V1\Sale\SaleResource;
use App\Http\Requests\Api\V1\Sale\StoreSaleRequest;
use App\Http\Requests\Api\V1\Sale\UpdateSaleRequest;

class SaleController extends Controller
{
    public function __construct(private SaleService $service) {}

    public function index()
    {
        return SaleResource::collection(
            $this->service->list()
        );
    }

// Cenario API Rest para salvar tabela com relacionamento
    // == Fluxo de negocio 
        // => Endpoint para venda  =  POST /api/v1/sales
        // O frontend envia algo assim:
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
        // ============================
    // =================== 
    
    // 1. Iniciar transaction
        //  DB::transaction(function () {
        //  ...
        //  });
        // Isso é obrigatório nesse cenário.
        //      Se algo falhar, tudo volta.
        //      Isso é padrão profissional.
    // ===================
    // 2. Criar a venda
        //  $sale = Sale::create([
        //      'client_id' => $request->client_id,
        //      'total_amount' => 0
        //  ]);
    // ================
    // 3. Salvar productos na pivot
        // $totalAmount = 0;
        // foreach ($request->products as $item) {
        //     $amount = $item['quantity'] * $item['unit_price'];
        //     $sale->products()->attach($item['product_id'], [
        //         'quantity' => $item['quantity'],
        //         'unit_price' => $item['unit_price'],
        //         'amount' => $amount
        //     ]);
        //     $totalAmount += $amount;
        // }
    // ============================
    // 4. Atualizar o total da venda
        // $sale->update([
        //     'total_amount' => $totalAmount
        // ]);
    // =============================
    // 5. Atualizar o total do client
        //  $client->update([
        //      'total_spent' => $client->total_spent + $totalAmount
        //  ]);
    // ==============================

    // Controller → recebe request
    // FormRequest → valida dados
    // Service → regra de negócio + transaction
    // Model → relacionamento + persistência
    // Resource → resposta da API
// ======================================================

    public function store(StoreSaleRequest $request)
    {
        $sale = $this->service->create($request->validated());

        return new SaleResource($sale);
    }

    public function show(Sale $sale)
    {
        return new SaleResource($sale);
    }

// Atualizacao de vendas conforme regra de negocio
    //  Uma venda pode ser atualizada de modo que :
        //  1 ou mais itens sejam substituidos
        //  1 ou mais itens sejam adicionados
        //  1 ou mais itens sejam excluidos
        //  O json recebido deve possuir o formato:
        //  {
        //      "replace" : [
        //                      [   
        //                          product_id: 1,
        //                          new_poduct: [
        //                                          id: 2,
        //                                          quantity: 2
        //                                      ]                   
        //                      ]
        //                 ],
        //      "insert": {
        //                  "product_id": 3,
        //                  "quantity": 2          
        //                }
        //      "destroy": [
        //                      {
        //                          "product_id": 4    
        //                      },
        //                      {
        //                          "product_id": 5
        //                      }
        //                 ]    
        //  }    
    //  -------------------------------------------
// ===============================================
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
