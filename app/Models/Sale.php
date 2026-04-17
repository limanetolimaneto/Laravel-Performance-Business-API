<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $table = 'sales';
    protected $fillable = [
        'client_id',
        'total_amount',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
