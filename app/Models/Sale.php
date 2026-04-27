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

    public function products(){
        return $this->belongsToMany(Product::class)->withPivot(['quantity','amount']);
    }


//  DOMAIN MINDSET NOTES 1

    // A model knows how to calculate its own total.

    public function recalculateTotal(): self
    {
        //  Using '$this->products()->get()->sum()' instead '$this->products->sum()'
        //      -   This ensures that the data in the database is up-to-date;
        //      -   '$this->products' may have a loaded relationship cache;
        //      -   This prevents inconsistency.
        $totalAmount = $this->products()->get()->sum(function ($product) {
            return $product->pivot->amount;
        });

        $this->update([
            'total_amount' => $totalAmount
        ]);

        return $this;
    }
//  ======================
}
