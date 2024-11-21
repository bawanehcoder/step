<?php

namespace App\Filament\Company\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'logs';

   

    public static function getTitle(Model $ownerRecord, string $pageClass): string
{
    return __('Log');
}

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('barcode')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('barcode')
            ->columns([
                Tables\Columns\TextColumn::make('barcode')->label(__('Barcode') ),
                Tables\Columns\TextColumn::make('order_status')->badge()->label(__('Status')),
                Tables\Columns\TextColumn::make('editby')->label(__('editby')),
                Tables\Columns\TextColumn::make('driver.name')->label(__('Driver')),
                Tables\Columns\TextColumn::make('updated_at')->label(__('Updated at')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
