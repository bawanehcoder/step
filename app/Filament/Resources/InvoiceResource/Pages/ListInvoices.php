<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Filament\Resources\InvoiceResource\Widgets\PaidIssuedInvoicesWidget;
use App\Filament\Resources\InvoiceResource\Widgets\PaidReceivedInvoicesWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected static ?string $title = 'Receipts';



    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            \pxlrbt\FilamentExcel\Actions\Pages\ExportAction::make(),

        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PaidReceivedInvoicesWidget::class,
            // PaidIssuedInvoicesWidget::class
        ];
    }
}
