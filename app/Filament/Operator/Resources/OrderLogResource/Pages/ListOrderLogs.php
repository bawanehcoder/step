<?php

namespace App\Filament\Operator\Resources\OrderLogResource\Pages;

use App\Filament\Operator\Resources\OrderLogResource;
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
