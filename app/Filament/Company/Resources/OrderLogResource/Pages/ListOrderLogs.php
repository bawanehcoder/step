<?php

namespace App\Filament\Company\Resources\OrderLogResource\Pages;

use App\Filament\Company\Resources\OrderLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrderLogs extends ListRecords
{
    protected static string $resource = OrderLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
