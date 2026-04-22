<?php

namespace App\Services;

use App\Models\Supplier;

class SupplierService 
{
   
    public function list()
    {
        return Supplier::query()->paginate(10);
    }

    public function create(array $data)
    {
        return Supplier::create($data);
    }
}

