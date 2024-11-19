<?php

namespace App\Filament\Operator\Resources\OrderResource\Pages;

use App\Events\OrderUpdated;
use App\Filament\Operator\Resources\OrderResource;
use App\Models\OrderLog;
use Filament\Actions;
use Filament\Pages\Actions\ButtonAction;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            ButtonAction::make('Print')
                ->label('Print Invoice')
                ->url(fn($record) => route('orders.print', $record))
                ->openUrlInNewTab(),
        ];
    }

    protected function afterSave(): void
    {
        // event(new OrderUpdated($this->record));

        // dd($this->record);
        $record = $this->record;
        $user = '';

        if (auth()->user()->id) {
            $user = auth()->user()->name;
        } else if (auth('operators')->user()->id) {
            $user = auth('operators')->user()->name;

        } else {
            $user = auth('companies')->user()->name;

        }

        OrderLog::create([
            'barcode' => $record->barcode,
            'customer_id' => $record->customer_id,
            'order_type_id' => $record->order_type_id,
            'delivery_option' => $record->delivery_option,
            'custom_delivery_date' => $record->custom_delivery_date,
            'order_description' => $record->order_description,
            'weight' => $record->weight,
            'number_of_pieces' => $record->number_of_pieces,
            'invoice_number' => $record->invoice_number,
            'invoice_value' => $record->invoice_value,
            'cash_required' => $record->cash_required,
            'order_notes' => $record->order_notes,
            'order_status' => $record->order_status,
            'company_id' => $record->company_id,
            'order_id' => $record->id,
            'driver_id' => $record->driver_id,
            'editby' =>$user
        ]);
    }
}