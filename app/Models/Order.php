<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Picqer\Barcode\BarcodeGeneratorPNG;

use Storage;

class Order extends Model
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
        'driver_id',
        'city_id',
        'zone_id',
        'total_amount',
        'phone_number',
        'pickup_from',
        'additional_details',
        'cash_note',
        'company_name',
        'customer_name',
        'phone2'

    ];

    protected $casts = [
        'order_status' => OrderStatus::class,
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {

            $barcodeDirectory = storage_path('app/barcodes');
            if (!file_exists($barcodeDirectory)) {
                mkdir($barcodeDirectory, 0755, true);
            }




            $max_id = \DB::table('orders')->find(\DB::table('orders')->max('id'))?->barcode;
            if ($max_id) {
                $order->barcode =  (string) $max_id + 1;

            } else {
                $order->barcode =  '2700000';

            }


            $user = User::find(1);
        //   dd( Notification::make()
        //   ->title('New Order')
        //   ->sendToDatabase(User::find(1)));

            // dd($user->notify(
            //     Notification::make()
            //         ->title('New Order #: ' . $order->barcode)
            //         ->toDatabase(),
            // ));

            


            // توليد رقم عشوائي مكون من تسعة أرقام
            // $order->barcode = str_pad(rand(0, 999999999), 9, '0', STR_PAD_LEFT);

            // توليد باركود بناءً على الرقم
            $generator = new BarcodeGeneratorPNG();
            // $generator->setBackgroundColor([0, 0, 255]);
            $barcode = $generator->getBarcode($order->barcode, $generator::TYPE_CODE_128);



            // حفظ الصورة في مسار داخل التخزين
            // $barcodePath = 'barcodes/' . $order->barcode . '.png';
            // // Storage::put($barcodePath, $barcode);
            // Storage::put($barcodePath, 'Hello World');

            // تخزين المسار في قاعدة البيانات
            $order->barcode_image = 'data:image/png;base64,' . base64_encode($barcode);
            

           
        });
    }

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
    // public function getCityIDAttribute()
    // {
    //     return $this->customer->city_id;
    // }

    public function getTotalAmountAttribute()
    {
        $deliveryPrice = $this?->zone?->price > 0 ? $this?->zone?->price : $this?->city?->price;
        $company = $this->company;
        $discount = 0;
        $weightPrice = 0;
        $totalWeightPrice = 0;

        // dd($deliveryPrice);

        $discounts = $company->discounts;

        if ($this->weight > 5) {

            if ($discounts) {
                if ($discounts->where('city_id', $this->city_id)) {
                    $weightPrice = $discounts->where('city_id', $this->city_id)->first()->price_per_kg ?? 0;
                }
                if ($discounts->where('zone_id', $this->zone_id)) {
                    $weightPrice = $discounts->where('zone_id', $this->zone_id)->first()->price_per_kg ?? 0;
                }
            } else {
                if ($this->zone) {
                    $weightPrice = $this->zone->price_per_kg;

                } else {
                    $weightPrice = $this->city->price_per_kg;
                }
            }


            $kg = (int) $this->weight - 5;
            // dd($kg);
            $totalWeightPrice = $kg * $weightPrice;
        }
        // dd($totalWeightPrice);
        try {
            if ($discounts->where('city_id', $this->city_id)) {
                $discount = $discounts->where('city_id', $this->city_id)->first()->value;
            }
            if ($discounts->where('zone_id', $this->zone_id)) {
                $discount = $discounts->where('zone_id', $this->zone_id)->first()->value;
            }
        } catch (\Throwable $th) {
            $discount = 0;
        }
        // dd($discount);

        return $this->cash_required - (($deliveryPrice + $totalWeightPrice) - $discount);
    }

    public function getDelAttribute()
    {
        $deliveryPrice = $this?->zone?->price > 0 ? $this?->zone?->price : $this?->city?->price;
        $company = $this->company;
        $discount = 0;
        $weightPrice = 0;
        $totalWeightPrice = 0;

        // dd($deliveryPrice);

        $discounts = $company->discounts;

        if ($this->weight > 5) {
            if ($discounts) {
                if ($discounts->where('city_id', $this->city_id)) {
                    $weightPrice = $discounts->where('city_id', $this->city_id)->first()->price_per_kg ?? 0;
                }
                if ($discounts->where('zone_id', $this->zone_id)) {
                    $weightPrice = $discounts->where('zone_id', $this->zone_id)->first()->price_per_kg ?? 0;
                }
            } else {
                if ($this->zone) {
                    $weightPrice = $this->zone->price_per_kg;

                } else {
                    $weightPrice = $this->city->price_per_kg;
                }
            }
            $kg = (int) $this->weight - 5;
            // dd($kg);
            $totalWeightPrice = $kg * $weightPrice;
        }
        // dd($totalWeightPrice);
        try {
            if ($discounts->where('city_id', $this->city_id)) {
                $discount = $discounts->where('city_id', $this->city_id)->first()->value;
            }
            if ($discounts->where('zone_id', $this->zone_id)) {
                $discount = $discounts->where('zone_id', $this->zone_id)->first()->value;
            }
        } catch (\Throwable $th) {
            $discount = 0;
        }
        // dd($discount);

        return (($deliveryPrice + $totalWeightPrice) - $discount);
    }

    public function logs()
    {
        return $this->hasMany(OrderLog::class);
    }

    public function invoices()
    {
        return $this->belongsToMany(Invoice::class);
    }

    public function scopeCompany(Builder $query, $id): void
    {
        $query->where('company_id', $id);
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
