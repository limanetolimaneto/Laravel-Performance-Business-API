<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;
    protected $table = 'clients';
    protected $fillable = [
        'name',
        'email',
        'phone',
        'total_spent',
    ];
    public function sales(){
        return $this->hasMany(Sale::class);
    }

//  DOMAIN MINDSET NOTES 2

    // A model knows how to calculate its own total.

    public function recalculateTotalSpent(): self
    {
        // It's much faster to use the total value field from the sales table instead of summing the pivot table.
        $totalSpent = $this->sales()->sum('total_amount');

        $this->update([
            'total_spent' => $totalSpent
        ]);

        return $this;
    }
//  ======================
}
