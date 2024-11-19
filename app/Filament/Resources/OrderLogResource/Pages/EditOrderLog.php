<?php

namespace App\Filament\Resources\OrderLogResource\Pages;

use App\Filament\Resources\OrderLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrderLog extends EditRecord
{
    protected static string $resource = OrderLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
