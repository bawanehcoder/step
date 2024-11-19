<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'barcode',
        'customer_id',
        'order_type_id',
        'delivery_option',
        'custom_delivery_date',
        'order_description',
        'weight',
        'number_of_pieces',
        'invoice_number',
        'invoice_value',
        'cash_required',
        'order_notes',
        'order_status',
        'company_id',
        'order_id',
        'editby',
        'driver_id'
    ];

    protected $casts = [
        'order_status' => OrderStatus::class,
    ];

    // علاقة مع الزبون
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // علاقة مع نوع الطلب
    public function orderType()
    {
        return $this->belongsTo(OrderType::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function getCityIDAttribute()
    {
        return $this->customer->city_id;
    }

    public function getTotalAmountAttribute()
    {
        $deliveryPrice = $this->zone->price ?? 0;
        $company = $this->company;
        $discount = 0;

        // if ($company->fixed_discount) {
        //     $discount = $company->fixed_discount;
        // } elseif ($company->percentage_discount) {
        //     $discount = ($deliveryPrice * $company->percentage_discount) / 100;
        // }

        return $this->cash_required + ($deliveryPrice - $discount);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function driver(){
        return $this->belongsTo(Driver::class);
    }
    public function geDriverNameAttribute(){
        return $this->driver->name;
    }
}
