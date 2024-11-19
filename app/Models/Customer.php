<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'phone', 'company_id', 'city_id', 'zone_id',
        'street_name', 'building_number', 'floor', 'additional_details'
    ];

    // علاقة مع الشركة
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // علاقة مع المدينة
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    // علاقة مع الزون
    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
}
