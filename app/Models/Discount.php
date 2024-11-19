<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = ['company_id', 'zone_id', 'value','default_price','price_per_kg'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function getDefaultPriceAttribute(){
        if($this->zone_id > 0){
            return $this->zone->price;
        }
        return $this->city->price;

    }
}
