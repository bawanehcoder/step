<?php

namespace App\Imports;

use App\Models\City;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Models\Zone;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Filament\Notifications\Notification;


class ImportOrder implements ToModel, WithHeadingRow
{

    public $company_id;

    public function __construct($company_id = null) {
        $this->company_id = $company_id;
    }

    private $rows = 0;
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {

        // dd($row);

        ++$this->rows;

        $company_id = $this->company_id;
        $recipient = User::find(1);
        if (auth('companies')?->user()?->id) {
            $company_id = $recipient = auth('companies')->user()->id;
        }

        $company = Company::find($company_id);

        $city_id = null;
        $city = City::where('name', $row['city_id'])
            ->orWhere('id', $row['city_id'])
            ->first();

        if ($city) {
            $city_id = $city->id;
        } else {

            Notification::make()
                ->title('Order #'. $this->rows .' not imported ')
                ->danger()
                // ->sendToDatabase($recipient)
                ->send();

            // return;
        }



        // dd( $user);

        try {

            // $zone_id = null;
            // $zone = Zone::where('name', $row['zone_id'])
            //     ->orWhere('id', $row['zone_id'])
            //     ->first();

            // if ($zone) {
                $zone_id = null;
            // } else {

                $zone_id = Zone::where('city_id', $city_id)->first()->id;
            // }





            $user_id = null;
            $user = Customer::where('id', $row['customer_id'])
                ->orWhere('name', $row['customer_id'])
                ->orWhere('phone', $row['customer_id'])->first();
            if ($user) {
                $user_id = $user->id;
            } else {
                $user = Customer::create([
                    'name' => $row['customer_id'],
                    'phone' => $row['phone_number'],
                    'company_id' => $company_id,
                    'city_id' => $city_id,
                    'zone_id' => $zone_id,
                    'street_name' => $zone_id,
                    'building_number' => $zone_id,
                    'floor' => $zone_id,
                    'additional_details' => $row['zone_id'],

                ]);
                $user_id = $user->id;
            }


            // dd($row);
            return new Order([
                'customer_id' => $user_id,
                'city_id' => $city_id,
                'zone_id' => $zone_id,
                'phone_number' => $row['phone_number'],
                'cash_required' => (double) $row['cash_required'],
                'invoice_number' => $row['invoice_number'] ?? null,
                'number_of_pieces' => (int) $row['number_of_pieces'],
                'order_notes' => $row['order_notes'],
                'company_id' => $company_id,
                'additional_details' => $row['zone_id'],
                'pickup_from' => $company->address

            ]);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
