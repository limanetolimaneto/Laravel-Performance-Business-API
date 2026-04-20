<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory;
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
