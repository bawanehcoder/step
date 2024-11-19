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
use Maatwebsite\Excel\Facades\Excel;


class ListOrders extends ListRecords
{
    use ExposesTableToWidgets;
    protected static string $resource = OrderResource::class;

    

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            \pxlrbt\FilamentExcel\Actions\Pages\ExportAction::make(),
            Action::make('import')
                ->label('Import Orders')
                ->form([
                    FileUpload::make('file')
                        ->label('Select CSV File')
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
            'pending pickup' => Tab::make()->query(fn($query) => $query->where('order_status', 'pending pickup')),
            'picked up' => Tab::make()->query(fn($query) => $query->where('order_status', 'picked up')),
            'ready for delivery' => Tab::make()->query(fn($query) => $query->where('order_status', 'ready for delivery')),
            'out for delivery' => Tab::make()->query(fn($query) => $query->where('order_status', 'out for delivery')),
            'delivered' => Tab::make()->query(fn($query) => $query->where('order_status', 'delivered')),
            'returned' => Tab::make()->query(fn($query) => $query->where('order_status', 'returned')),
            'damaged' => Tab::make()->query(fn($query) => $query->where('order_status', 'damaged')),
        ];
    }
}
