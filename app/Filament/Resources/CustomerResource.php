<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use App\Models\Zone;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationGroup = 'Users';

    protected static ?string $navigationLabel = 'Customer Information';
    protected static ?string $label = 'Customer Information';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                TextInput::make('phone')->required(),
                Select::make('company_id')
                    ->relationship('company', 'name')->required(),  // اختيار الشركة
                Select::make('city_id')
                    ->relationship('city', 'name')                  // اختيار المدينة
                    ->required()
                    ->reactive()  // تحديد الحقل على أنه تفاعلي
                    ->afterStateUpdated(fn($set) => $set('zone_id', null)),  // إعادة ضبط حقل الزون عند تغيير المدينة
                Select::make('zone_id')
                    ->options(function (callable $get) {
                        $cityId = $get('city_id');  // الحصول على معرف المدينة المختارة
                        if (!$cityId) {
                            return [];  // إذا لم تكن هناك مدينة مختارة، لا تعرض شيئًا
                        }
                        return Zone::where('city_id', $cityId)->pluck('name', 'id');  // جلب الزون المرتبطة بالمدينة
                    })
                    ->required()
                    ->label('Zone')
                    ->disabled(fn(callable $get) => !$get('city_id')),  // تعطيل الحقل إذا لم يتم اختيار مدينة
                TextInput::make('street_name')->required(),
                TextInput::make('building_number')->required(),
                TextInput::make('floor')->required(),
                Textarea::make('additional_details')->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('phone')->sortable()->searchable(),
                TextColumn::make('company.name')->label('Company')->sortable(),
                TextColumn::make('city.name')->label('City')->sortable(),
                TextColumn::make('zone.name')->label('Zone')->sortable(),
                TextColumn::make('street_name')->label('Street')->sortable(),
                TextColumn::make('building_number')->label('Building')->sortable(),
                TextColumn::make('floor')->label('Floor')->sortable(),
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

                    Filter::make('phone')
                        ->label('Phone Number')
                        ->form([
                            TextInput::make('phone'),

                        ])
                        ->query(function (Builder $query, array $data): Builder {
                            return $query->where('phone', 'like', '%' . $data['phone'] . '%');
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


            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
