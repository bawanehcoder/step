<?php

namespace App\Filament\Resources\OrderLogResource\Pages;

use App\Filament\Resources\OrderLogResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrderLog extends CreateRecord
{
    protected static string $resource = OrderLogResource::class;
}
