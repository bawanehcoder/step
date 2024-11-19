<?php

namespace App\Filament\Operator\Resources\OrderLogResource\Pages;

use App\Filament\Operator\Resources\OrderLogResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrderLog extends CreateRecord
{
    protected static string $resource = OrderLogResource::class;
}
