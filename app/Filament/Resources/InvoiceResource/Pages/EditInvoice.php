<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Pages\Actions\ButtonAction;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected static ?string $title = 'Receipts';


    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            ButtonAction::make('Print')
                ->label('Print Invoice')
                ->url(fn($record) => route('invoices.print', $record))
                ->openUrlInNewTab(),
        ];
    }
}
