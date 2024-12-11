<?php

namespace App\Filament\Company\Resources\OrderResource\Pages;

use App\Filament\Company\Resources\OrderResource;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function afterSave(): void
    {
        Notification::make()
          ->title('New Order');
           Notification::make()
          ->title('New Order')
          ->sendToDatabase(User::find(1));
    }
}
