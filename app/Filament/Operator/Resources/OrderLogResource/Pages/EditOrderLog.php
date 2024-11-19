<?php

namespace App\Filament\Operator\Resources\OrderLogResource\Pages;

use App\Filament\Operator\Resources\OrderLogResource;
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
