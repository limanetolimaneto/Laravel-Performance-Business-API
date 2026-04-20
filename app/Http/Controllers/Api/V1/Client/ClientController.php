<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\ClientService;
use App\Http\Resources\Api\V1\Client\ClientResource;
use App\Http\Requests\Api\V1\Client\StoreClientRequest;
use App\Http\Requests\Api\V1\Client\UpdateClientRequest;

class ClientController extends Controller
{
    public function __construct(private ClientService $service) {}

    /*
    |--------------------------------------------------------------------------
    | Performance Note
    |--------------------------------------------------------------------------
    |
    | See README:
    | Section 1 - N+1 Query Problem
    | Section 2 - Eager Loading Optimization
    |
    */
    public function index()
    {
        return ClientResource::collection(
            $this->service->list()
        );
    }

    public function store(StoreClientRequest $request)
    {
        $client = $this->service->create($request->validated());

        return new ClientResource($client);
    }

    public function show(Client $client)
    {
        return new ClientResource($client);
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        $client = $this->service->update($client, $request->validated());

        return new ClientResource($client);
    }

    public function destroy(Client $client)
    {
        $this->service->delete($client);

        return response()->json(['message' => 'Deleted']);
    }
}