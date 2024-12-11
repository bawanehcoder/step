<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;



class Company extends Authenticatable implements FilamentUser
{
    use HasFactory;
    protected $fillable = [
        'name',
        'image',
        'phone',
        'address',
        'email',
        'password',
        'is_blocked',
        'city_id',
        'zone_id',
        'contact',
        'phone2'
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return true; // you can add your own logic to access driver users.
    }

    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];


    public function discounts()
    {
        return $this->hasMany(Discount::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

}
