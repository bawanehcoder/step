<?php

namespace App\Filament\Company\Resources;

use App\Filament\Company\Resources\OrderLogResource\Pages;
use App\Filament\Company\Resources\OrderLogResource\RelationManagers;
use App\Models\OrderLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderLogResource extends Resource
{
    protected static ?string $model = OrderLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Tracking';

    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()->where('company_id', auth()->user()->id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('barcode')->searchable(),
                Tables\Columns\TextColumn::make('order_notes'),
                Tables\Columns\TextColumn::make('order_status')->badge(),
                Tables\Columns\TextColumn::make('created_at'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('view')
                    ->label('View Order')
                    ->url(fn ($record) => route('filament.resources.orders.show2', $record->order_id)), // افترض أن لديك علاقة order_id
            
            ])
            ->bulkActions([
              
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrderLogs::route('/'),
            // 'create' => Pages\CreateOrderLog::route('/create'),
            // 'edit' => Pages\EditOrderLog::route('/{record}/edit'),
        ];
    }
}
