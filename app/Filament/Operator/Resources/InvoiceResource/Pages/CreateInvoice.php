<?php

namespace App\Filament\Operator\Resources\InvoiceResource\Pages;

use App\Filament\Operator\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected static ?string $title = 'Receipts';

}
