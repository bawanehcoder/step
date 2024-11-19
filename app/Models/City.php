<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'price','price_per_kg'];  

    public function zones()
    {
        return $this->hasMany(Zone::class);
    }
}
