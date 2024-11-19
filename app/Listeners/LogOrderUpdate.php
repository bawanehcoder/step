<?php

namespace App\Listeners;

use App\Events\OrderUpdated;
use App\Models\OrderLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogOrderUpdate
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderUpdated $event)
    {
        $order = $event->order;
        dd('a');

        // تسجيل اللوج
        OrderLog::create([
            'barcode' => $order->barcode,
            'customer_id' => $order->customer_id,
            'order_type_id' => $order->order_type_id,
            'delivery_option' => $order->delivery_option,
            'custom_delivery_date' => $order->custom_delivery_date,
            'order_description' => $order->order_description,
            'weight' => $order->weight,
            'number_of_pieces' => $order->number_of_pieces,
            'invoice_number' => $order->invoice_number,
            'invoice_value' => $order->invoice_value,
            'cash_required' => $order->cash_required,
            'order_notes' => $order->order_notes,
            'order_status' => $order->order_status,
            'company_id' => $order->company_id,
            'order_id' => $order->id,
        ]);
    }
}
