<?php

namespace App\Filament\Imports;

use App\Imports\Importer;
use App\Models\City;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Zone;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use Log;

class OrderImporter extends Importer
{
    protected static ?string $model = Order::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id'),
            ImportColumn::make('customer_id'),
            ImportColumn::make('city_id'),
            ImportColumn::make('zone_id'),
            ImportColumn::make('phone_number'),
            ImportColumn::make('cash_required'),
            ImportColumn::make('invoice_number'),
            ImportColumn::make('number_of_pieces'),
            ImportColumn::make('order_notes'),
        ];
    }

    public function resolveRecord(): ?Order
    {
        $company_id = Company::first()->id;
        if (auth('companies')?->user()?->id) {
            $company_id = auth('companies')->user()->id;
        }

        $city_id = null;
        $city  = City::where('name', $this->data['city_id'])
            ->orWhere('id', $this->data['city_id'])
            ->first();

        if ($city) {
            $city_id = $city->id;
        } else {
            $city = City::create([
                'name' => $this->data['city_id'],
                'price' => 3,
            ]);
            $city_id = $city->id;

        }


        $zone_id = null;
        $zone = Zone::where('name', $this->data['zone_id'])
            ->orWhere('id', $this->data['zone_id'])
            ->first();

        if ($zone) {
            $zone_id = $zone->id;
        } else {
            $zone = Zone::create([
                'name' => $this->data['zone_id'],
                'city_id' => $city_id,
                'price' => 3,
            ]);
            $zone_id = $zone->id;

        }





        $user_id = null;
        $user = Customer::where('id', $this->data['customer_id'])
            ->orWhere('name', $this->data['customer_id'])
            ->orWhere('phone', $this->data['customer_id'])->first();
            // dd( $user);

        if ($user) {
            $user_id = $user->id;
        } else {
            $user = Customer::create([
                'name' => $this->data['customer_id'],
                'phone' => $this->data['phone_number'],
                'company_id' => $company_id,
                'city_id' => $city_id,
                'zone_id' => $zone_id,
                'street_name' => $zone_id,
                'building_number' => $zone_id,
                'floor' => $zone_id,

            ]);
            $user_id = $user->id;
        }


        // dd($this->data);
         new Order([
            'customer_id' => $user_id,
            'city_id' => $city_id,
            'zone_id' => $zone_id,
            'phone_number' => $this->data['phone_number'],
            'cash_required' => (double) $this->data['cash_required'],
            'invoice_number' => $this->data['invoice_number'],
            'number_of_pieces' => (int) $this->data['number_of_pieces'],
            'order_notes' => $this->data['order_notes'],
            'company_id' => $company_id,
         ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        return 'Your order import has completed.';
    }
}
