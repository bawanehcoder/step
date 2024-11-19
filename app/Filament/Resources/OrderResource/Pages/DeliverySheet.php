<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Resources\Pages\Page;

class DeliverySheet extends Page
{
    protected static string $resource = OrderResource::class;

    protected static string $view = 'filament.resources.order-resource.pages.delivery-sheet';

    public $orders; // لتخزين الطلبات

    public function mount(array $order_ids)
    {
        $this->orders = Order::with(['customer', 'city', 'zone'])
            ->whereIn('id', $order_ids)
            ->get();
    }

//     public function render()
//     {
//         return view('filament.pages.delivery-sheet', [
//             'orders' => $this->orders,
//         ]);
//     }
}
