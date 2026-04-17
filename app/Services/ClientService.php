<?php

namespace App\Services;

use App\Models\Client;

class ClientService
{
    public function list()
    {
        return Client::query()->paginate(10);
    }

    public function create(array $data)
    {
        return Client::create($data);
    }

    public function update(Client $client, array $data)
    {
        $client->update($data);
        return $client;
    }

    public function delete(Client $client)
    {
        $client->delete();
    }
}