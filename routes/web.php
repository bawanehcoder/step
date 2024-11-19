<?php

use App\Filament\Pages\TrackOrder;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\Pages\DeliverySheet;
use App\Filament\Resources\OrderResource\Pages\EditOrder;
use App\Http\Controllers\ExelController;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('orders/{record}', EditOrder::class)->name('filament.resources.orders.show');
Route::get('company/orders/{record}', function ($record) {
    return redirect('company/orders/' . $record . '/edit');
})->name('filament.resources.orders.show2');


Route::get('/invoices/{invoice}/print', function (Invoice $invoice) {
    return view('invoices.print-invoice', compact('invoice'));
})->name('invoices.print');


Route::get('/orders/{order}/print', function (Order $order) {
    return view('invoices.order-details', compact('order'));
})->name('orders.print');



Route::get('/orders-print/{order}/print', function ($order) {
    return view('invoices.orders-details', compact('order'));
})->name('orders.prints');


Route::get('/delivery-sheet', function(Request $request){
    // Retrieve the selected orders
    $orders = Order::with(['customer', 'city', 'zone'])->whereIn('id', $request->order_ids)->get();

    return view('invoices.ds', compact('orders'));
})->name('delivery.sheet');


Route::get('/delivery-sheet', DeliverySheet::class)->name('filament.delivery-sheet');



Route::get('/file-import',[ExelController::class,'importView'])->name('import-view');
Route::post('/import',[ExelController::class,'import'])->name('import');
