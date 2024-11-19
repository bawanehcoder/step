<?php

namespace App\Filament\Company\Resources;

use App\Filament\Company\Resources\CustomerResource\Pages;
use App\Filament\Company\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use App\Models\Zone;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()->where('company_id', auth()->user()->id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                TextInput::make('phone')->required(),
                TextInput::make('company_id')
                    ->default(auth()->user()->id)
                    ->hidden(),  // اختيار الشركة
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
                TextColumn::make('name')->sortable(),
                TextColumn::make('phone')->sortable(),
                // TextColumn::make('company.name')->label('Company')->sortable(),
                TextColumn::make('city.name')->label('City')->sortable(),
                TextColumn::make('zone.name')->label('Zone')->sortable(),
                TextColumn::make('street_name')->label('Street')->sortable(),
                TextColumn::make('building_number')->label('Building')->sortable(),
                TextColumn::make('floor')->label('Floor')->sortable(),
            ])
            ->filters([
                //
            ])
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
