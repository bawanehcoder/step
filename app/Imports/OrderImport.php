<?php

namespace App\Imports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\ToModel;

class OrderImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Order([
            'customer_id' => $row['customer_id'],
            'city_id' => $row['city_id'],
            'zone_id' => $row['zone_id'],
            'phone_number' => $row['phone_number'],
            'cash_required' => $row['cash_required'],
            'invoice_number' => $row['invoice_number'],
            'number_of_pieces' => $row['number_of_pieces'],
            'order_notes' => $row['order_notes'],
        ]);
    }

    public function rules(): array
    {
        return [
            '*.customer_id' => 'required',
            '*.city_id' => 'required',
            '*.zone_id' => 'required',
            '*.phone_number' => 'nullable|max:255',
            '*.cash_required' => 'required|numeric',
            '*.invoice_number' => 'required',
            '*.number_of_pieces' => 'required|integer',
            '*.order_notes' => 'nullable',
        ];
    }
}
