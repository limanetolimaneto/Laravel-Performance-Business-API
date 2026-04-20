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

}
