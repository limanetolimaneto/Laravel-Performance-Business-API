<?php

namespace App\Http\Controllers\Api\V1\Supplier;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Services\SupplierService;
use App\Http\Resources\Api\V1\Supplier\SupplierResource;
use App\Http\Requests\Api\V1\Supplier\StoreSupplierRequest;
use App\Http\Requests\Api\V1\Supplier\UpdateSupplierRequest;

class SupplierController extends Controller
{
    public function __construct(private SupplierService $service) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return SupplierResource::collection( $this->service->list() );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSupplierRequest $request)
    {
        $supplier = $this->service->create($request->validated());

        return new SupplierResource($supplier);
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        return new SupplierResource($supplier);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
          $supplier = $this->service->update($supplier, $request->validated());

        return new SupplierResource($supplier);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        $this->service->delete($supplier);

        return response()->json(['message' => 'Deleted']);
    }
}
