<?php

namespace App\Services;

use App\Models\Product;

class ProductService 
{
   
    public function list()
    {
        return Product::query()->paginate(10);
    }

    public function create(array $data)
    {
        return Product::create($data);
    }
}
