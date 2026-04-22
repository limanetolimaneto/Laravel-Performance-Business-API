<?php

namespace App\Http\Resources\Api\V1\Supplier;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'nationality' => $this->nationality,
            'phone_numbber' => $this->phone_numbber,
        ];
    }
}