<?php

namespace App\Filament\Company\Widgets;

use App\Filament\Company\Resources\OrderResource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrders extends BaseWidget
{

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                OrderResource::getEloquentQuery()
            )
            ->columns([
                TextColumn::make('barcode')->sortable(),  // عرض بار كود الطلب
                TextColumn::make('customer.name')->label('Customer')->sortable(),  // عرض اسم الزبون
                TextColumn::make('delivery_option')->sortable(),  // عرض خيار التسليم
                TextColumn::make('cash_required')->sortable()
                ->summarize([
                    Tables\Columns\Summarizers\Sum::make()
                        ->money(),
                ]), 
                 // عرض قيمة الكاش المطلوبة
                TextColumn::make('order_status')->badge(), // عرض حالة الطلب

                TextColumn::make('total_amount')
                    ->label('Total Amount Required')
                    
                    ->sortable(),
            ]);
    }
}
