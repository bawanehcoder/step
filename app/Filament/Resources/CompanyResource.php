<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyOrdersRelationManagerResource\RelationManagers\OrdersRelationManager;
use App\Filament\Resources\CompanyResource\Pages;
use App\Filament\Resources\CompanyResource\RelationManagers;
use App\Models\City;
use App\Models\Company;
use App\Models\Zone;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Users';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                FileUpload::make('image')->image()->nullable(), // لرفع الصور
                TextInput::make('phone')->required(),
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
                    ->disabled(fn(callable $get) => !$get('city_id')),
                TextInput::make('address')->required(),
                TextInput::make('email')
                    ->label('Email')
                    ->required()
                    ->unique(ignoreRecord: true), // التأكد من فريدة اسم المستخدم

                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->dehydrateStateUsing(fn($state) => bcrypt($state)),
                Toggle::make('is_blocked')
                    ->label('Blocked')
                    ->default(false)
                    ->inline(false),

                Repeater::make('discounts')
                    ->relationship('discounts')
                    ->schema([
                        Select::make('city_id')
                            ->label('City')
                            ->relationship('city', 'name') // استخدام العلاقة مع المدن
                            ->required()
                            ->reactive() // يجعل الحقل ديناميكيًا
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                $set('zone_id', null);

                                $cityID = $get('city_id'); // الحصول على قيمة الزون المختارة
                                if ($cityID) {
                                    // جلب السعر الافتراضي للزون المحددة
                                    $cityPrice = City::find($cityID)?->price; // افتراض أن هناك حقل `price` في جدول الزونات
                                    $set('default_price', $cityPrice); // تعيين السعر الافتراضي في حقل `default_price`
                                }
                            }), // إعادة تعيين قيمة الزون عند تغيير المدينة

                        Select::make('zone_id')
                            ->label('Zone')
                            ->options(function (callable $get) {
                                $cityId = $get('city_id'); // الحصول على قيمة المدينة المختارة
                                if ($cityId) {
                                    // إرجاع الزونات المتاحة بناءً على المدينة المختارة
                                    return Zone::where('city_id', $cityId)->pluck('name', 'id');
                                }
                                return []; // إرجاع قائمة فارغة إذا لم يتم اختيار مدينة
                            })
                            // ->required()
                            ->reactive() // يجعل الحقل ديناميكيًا عند تغيير الزون
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                $zoneId = $get('zone_id'); // الحصول على قيمة الزون المختارة
                                if ($zoneId) {
                                    // جلب السعر الافتراضي للزون المحددة
                                    $zonePrice = Zone::find($zoneId)?->price; // افتراض أن هناك حقل `price` في جدول الزونات
                                    $set('default_price', $zonePrice); // تعيين السعر الافتراضي في حقل `default_price`
                                }
                            }),

                        TextInput::make('default_price')
                            ->label('Default Price')
                            ->numeric()
                            ->readOnly(), // جعل الحقل غير قابل للتعديل لأنه للعرض فقط

                        TextInput::make('value')
                            ->label('Discount Value')
                            ->numeric()
                            ->required(),

                        TextInput::make('price_per_kg')
                            ->label('KG Price after 5')
                            ->numeric()
                            ->required(),
                    ])
                    ->columns(4)
                    ->createItemButtonLabel('Add Discount')->columnSpan(2),
            ]);


    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                ImageColumn::make('image'), // لعرض الصور
                TextColumn::make('phone')->sortable()->searchable(),
                TextColumn::make('address')->sortable(),
                BadgeColumn::make('is_blocked')
                    ->label('Status')
                    ->colors([
                        'danger' => 'Blocked', // اللون الأحمر عندما تكون الشركة محظورة
                        'success' => 'Active', // اللون الأخضر عندما تكون الشركة غير محظورة
                    ])
                    ->getStateUsing(fn($record) => $record->is_blocked ? 'Blocked' : 'Active'),
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
            OrdersRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
