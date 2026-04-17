<?php

namespace App\Services;

use App\Models\Sale;

class SaleService
{
    public function list()
    {
        // PROPOSITALMENTE NÃO OTIMIZADO
        return Sale::query()->paginate(10);
    }

    public function create(array $data)
    {
        return Sale::create($data);
    }
}