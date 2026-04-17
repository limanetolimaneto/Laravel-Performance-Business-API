<?php

namespace App\Http\Resources\Api\V1\Sale;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'total_amount' => $this->total_amount,
            'sale_date' => $this->sale_date,

            // 🔥 aqui começa o N+1
            'client' => [
                'id' => $this->client->id,
                'name' => $this->client->name,
            ]
        ];
    }
}
