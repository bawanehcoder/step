<?php

namespace App\Filament\Company\Resources\OrderResource\Pages;

use App\Filament\Company\Resources\OrderResource;
use App\Imports\ImportOrder;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Components\Tab;

use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Illuminate\Contracts\Support\Htmlable;
use Maatwebsite\Excel\Facades\Excel;


class ListOrders extends ListRecords
{
    use ExposesTableToWidgets;
    protected static string $resource = OrderResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('Orders');
    }


    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            \pxlrbt\FilamentExcel\Actions\Pages\ExportAction::make(),
            Action::make('import')
                ->label(__('Import Orders'))
                ->form([
                    FileUpload::make('file')
                        ->label(__('Select CSV File'))
                        ->directory('temp') // Save the file temporarily
                        ->required()
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel', 'application/csv']),
                ])
                ->action(function (array $data) {
                    // Get the relative file path from the FileUpload component
                    $relativePath = 'app/public/' . $data['file'];
                    $filePath = storage_path($relativePath); // Builds the full path
        
                    //Check if the file exists before importing
                    if (!file_exists($filePath)) {
                        Notification::make()
                            ->title('File does not exist for import.')
                            ->danger()
                            ->send();
                        return;
                    }
                    Excel::import(new ImportOrder, $filePath);
                    Notification::make()
                        ->title('Orders imported successfully')
                        ->success()
                        ->send();


                }),
        ];
    }
    protected function getHeaderWidgets(): array
    {
        return OrderResource::getWidgets();
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make('All'),
            __('pending pickup') => Tab::make()->query(fn($query) => $query->where('order_status', 'pending pickup')),
            __('picked up') => Tab::make()->query(fn($query) => $query->where('order_status', 'picked up')),
            __('ready for delivery') => Tab::make()->query(fn($query) => $query->where('order_status', 'ready for delivery')),
            __('out for delivery') => Tab::make()->query(fn($query) => $query->where('order_status', 'out for delivery')),
            __('delivered') => Tab::make()->query(fn($query) => $query->where('order_status', 'delivered')),
            __('returned') => Tab::make()->query(fn($query) => $query->where('order_status', 'returned')),
            __('damaged') => Tab::make()->query(fn($query) => $query->where('order_status', 'damaged')),
        ];
    }
}
