<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Filament\Panel;



class Driver extends Authenticatable implements FilamentUser
{
    use HasFactory;

    protected $fillable = [
        'name',
        'car_type',
        'image',
        'phone_number',
        'password', // إضافة كلمة المرور
        'email', // البريد الإلكتروني
        'car_number',
        'price_per_order',
        'blocked'
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
}
