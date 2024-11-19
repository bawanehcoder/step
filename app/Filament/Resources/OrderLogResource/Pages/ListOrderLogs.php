<?php

namespace App\Filament\Resources\OrderLogResource\Pages;

use App\Filament\Resources\OrderLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrderLogs extends ListRecords
{
    protected static string $resource = OrderLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            \pxlrbt\FilamentExcel\Actions\Pages\ExportAction::make(),

        ];
    }
}
