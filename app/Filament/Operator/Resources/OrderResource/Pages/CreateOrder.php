<?php

namespace App\Filament\Operator\Resources\OrderResource\Pages;

use App\Filament\Operator\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
}
