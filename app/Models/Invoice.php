<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'beneficiary_type', 'description', 'invoice_date','amount','beneficiary_id','status','additional_notes'];

    // علاقة الفاتورة مع الطلبات
    public function orders()
    {
        return $this->belongsToMany(Order::class);
    }

    public function beneficiary(){
        if($this->beneficiary_type == 'driver'){
            return $this->belongsTo(Driver::class);
        }
        return $this->belongsTo(Company::class);

    }
}
