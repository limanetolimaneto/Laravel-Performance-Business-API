<?php

namespace App\Http\Controllers\Api\V1\Sale;

use App\Http\Controllers\Controller;
use App\Services\SaleService;
use App\Http\Resources\Api\V1\Sale\SaleResource;
use App\Http\Requests\Api\V1\Sale\StoreSaleRequest;

class SaleController extends Controller
{
    public function __construct(private SaleService $service) {}

    public function index()
    {
        return SaleResource::collection(
            $this->service->list()
        );
    }

    public function store(StoreSaleRequest $request)
    {
        $sale = $this->service->create($request->validated());

        return new SaleResource($sale);
    }
}
