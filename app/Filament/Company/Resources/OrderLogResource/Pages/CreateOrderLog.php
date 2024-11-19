<?php

namespace App\Filament\Company\Resources\OrderLogResource\Pages;

use App\Filament\Company\Resources\OrderLogResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrderLog extends CreateRecord
{
    protected static string $resource = OrderLogResource::class;
}
