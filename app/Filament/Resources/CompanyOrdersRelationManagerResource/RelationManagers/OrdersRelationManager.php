<?php

namespace App\Filament\Resources\CompanyOrdersRelationManagerResource\RelationManagers;

use App\Models\Zone;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('barcode')
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





            ])
            ->filters(
                [

                    SelectFilter::make('city_id')
                    ->multiple()
                        ->label('City')
                        ->options(function () {
                            return Zone::all()->pluck('name', 'id');
                        })
                        ->searchable()
                        ->query(function (Builder $query, array $data) {
                            if (isset($data['value'])) {
                                $query->where('city_id', $data['value']);
                            }
                        }),

                    SelectFilter::make('zone_id')
                    ->multiple()
                        ->label('Zone')
                        ->options(function () {
                            return Zone::all()->pluck('name', 'id');
                        })
                        ->searchable()
                        ->query(function (Builder $query, array $data) {
                            if (isset($data['value'])) {
                                $query->where('zone_id', $data['value']);
                            }
                        }),

                    Filter::make('phone_number')
                        ->label('Phone Number')
                        ->form([
                            TextInput::make('phone_number'),

                        ])
                        ->query(function (Builder $query, array $data): Builder {
                            return $query->where('phone_number', 'like', '%' . $data['phone_number'] . '%');
                        }),


                    Filter::make('created_at')
                        ->form([
                            DatePicker::make('created_from'),
                            DatePicker::make('created_until'),
                        ])->columnSpan(2)->columns(2)
                        ->query(function (Builder $query, array $data): Builder {
                            return $query
                                ->when(
                                    $data['created_from'],
                                    fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                                )
                                ->when(
                                    $data['created_until'],
                                    fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                                );
                        })


                ],
                layout: FiltersLayout::AboveContent
            )->columns([
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





            ])
            ->filters(
                [

                    SelectFilter::make('city_id')
                    ->multiple()
                        ->label('City')
                        ->options(function () {
                            return Zone::all()->pluck('name', 'id');
                        })
                        ->searchable()
                        ->query(function (Builder $query, array $data) {
                            if (isset($data['value'])) {
                                $query->where('city_id', $data['value']);
                            }
                        }),

                    SelectFilter::make('zone_id')
                    ->multiple()
                        ->label('Zone')
                        ->options(function () {
                            return Zone::all()->pluck('name', 'id');
                        })
                        ->searchable()
                        ->query(function (Builder $query, array $data) {
                            if (isset($data['value'])) {
                                $query->where('zone_id', $data['value']);
                            }
                        }),

                    Filter::make('phone_number')
                        ->label('Phone Number')
                        ->form([
                            TextInput::make('phone_number'),

                        ])
                        ->query(function (Builder $query, array $data): Builder {
                            return $query->where('phone_number', 'like', '%' . $data['phone_number'] . '%');
                        }),


                    Filter::make('created_at')
                        ->form([
                            DatePicker::make('created_from'),
                            DatePicker::make('created_until'),
                        ])->columnSpan(2)->columns(2)
                        ->query(function (Builder $query, array $data): Builder {
                            return $query
                                ->when(
                                    $data['created_from'],
                                    fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                                )
                                ->when(
                                    $data['created_until'],
                                    fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                                );
                        })


                ],
                layout: FiltersLayout::AboveContent
            )
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
