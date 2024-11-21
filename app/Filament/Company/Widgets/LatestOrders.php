<?php

namespace App\Filament\Company\Widgets;

use App\Filament\Company\Resources\OrderResource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;

class LatestOrders extends BaseWidget
{


    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function getTableHeading(): string | Htmlable | null
    {
        return __('Latest Orders');
    }
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                OrderResource::getEloquentQuery()
            )
            ->columns([
                TextColumn::make (__('barcode'))->sortable() 
                ->label(__('Barcode')),

                TextColumn::make (__('customer.name'))->label(__('Customer'))->sortable(), 
               
                TextColumn::make (__('delivery_option')) ->sortable()
                ->label(__('Delivery Option')),

                TextColumn::make(('cash_required'))->sortable()
                ->label(__('Cash Required'))

                ->summarize([
                    Tables\Columns\Summarizers\Sum::make()
                        ->money()
                        
                ]), 
                 // عرض قيمة الكاش المطلوبة
                TextColumn::make(__('order_status')) ->badge() // عرض حالة الطلب
                ->label(__('Order Status')),

                TextColumn::make(__('total_amount'))
                    ->label(__('Total Amount'))
                    
                    ->sortable(),
            ]);
    }
}
