<?php

namespace App\Filament\Exports;

use App\Models\Order;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class OrderExporter extends Exporter
{
    protected static ?string $model = Order::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('barcode'),
            // ExportColumn::make('customer_id'),
            // ExportColumn::make('order_type_id'),
            // ExportColumn::make('delivery_option'),
            // ExportColumn::make('custom_delivery_date'),
            // ExportColumn::make('order_description'),
            // ExportColumn::make('weight'),
            // ExportColumn::make('number_of_pieces'),
            // ExportColumn::make('invoice_number'),
            // ExportColumn::make('invoice_value'),
            // ExportColumn::make('cash_required'),
            // ExportColumn::make('total_amount'),
            // ExportColumn::make('order_notes'),
            // ExportColumn::make('order_status'),
            // ExportColumn::make('created_at'),
            // ExportColumn::make('updated_at'),
            // ExportColumn::make('company_id'),
            // ExportColumn::make('city_id'),
            // ExportColumn::make('zone_id'),
            // ExportColumn::make('driver_id'),
            // ExportColumn::make('phone_number'),
            // ExportColumn::make('pickup_from'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your order export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
