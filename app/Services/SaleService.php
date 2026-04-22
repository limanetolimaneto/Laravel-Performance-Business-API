<?php

namespace App\Services;

use App\Models\Sale;

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

    public function create(array $data)
    {
        return Sale::create($data);
    }



    public function listLazy()
    {
        return Sale::latest()->paginate(10);
    }

    public function listEager()
    {
        return Sale::with('client')
            ->latest()
            ->paginate(10);
    }

}