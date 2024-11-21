<?php

namespace App\Filament\Company\Resources;

use App\Enums\OrderStatus;
use App\Events\OrderUpdated;
use App\Filament\Company\Resources\OrderResource\Pages;
use App\Filament\Company\Resources\OrderResource\Pages\CreateOrder;
use App\Filament\Company\Resources\OrderResource\Pages\EditOrder;
use App\Filament\Company\Resources\OrderResource\Pages\ListOrders;
use App\Filament\Company\Resources\OrderResource\RelationManagers;
use App\Filament\Company\Resources\OrderResource\RelationManagers\OrderLogsRelationManager;
use App\Filament\Company\Resources\OrderResource\Widgets\OrderOverview;
use App\Models\City;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Order;
use App\Models\OrderLog;
use App\Models\Zone;
use Doctrine\DBAL\Schema\Column;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use Str;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationLabel(): string
    {
        return __('Orders');
    }

    public static function getModelLabel(): string
    {
        return __('Order');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Orders');
    }


    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()->where('company_id', auth('companies')->user()->id);
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Section::make()->schema([
                            TextInput::make('barcode')
                                ->label(__('Barcode'))
                                ->required()

                                ->maxLength(12) // التأكد من أن الطول 12 خانة
                                ->readOnly()
                                ->default(function () {
                                    $max_id = \DB::table('orders')->find(\DB::table('orders')->max('id'))?->barcode;
                                    if ($max_id) {
                                        return (string) $max_id + 1;

                                    } else {
                                        return '270000000';

                                    }
                                })->disabled(fn($get) => $get('id') != null)
                                ->columnSpan(2), // تعيين اسم الحقل/ توليد بار كود تلقائي

                            Forms\Components\Select::make('customer_id')
                                ->label(__('Customer'))

                                ->relationship('customer', 'name')
                                ->searchable()
                                ->preload()

                                ->createOptionForm([

                                    TextInput::make('name')->required()
                                        ->label(__('Name')),
                                    TextInput::make('phone')->required()
                                        ->label(__('Phone')),

                                    TextInput::make('company_id')
                                        ->label(__('Company_'))

                                        ->default(auth('companies')->user()->id)
                                        ->readOnly(),
                                    Select::make('city_id')
                                        ->label(__('City_'))

                                        ->relationship('city', 'name')                  // اختيار المدينة
                                        ->required()
                                        ->reactive()  // تحديد الحقل على أنه تفاعلي
                                        ->afterStateUpdated(fn($set) => $set('zone_id', null)),  // إعادة ضبط حقل الزون عند تغيير المدينة
                                    Select::make('zone_id')
                                        ->label(__('Zone'))

                                        ->options(function (callable $get) {
                                            $cityId = $get('city_id');  // الحصول على معرف المدينة المختارة
                                            if (!$cityId) {
                                                return [];  // إذا لم تكن هناك مدينة مختارة، لا تعرض شيئًا
                                            }
                                            return Zone::where('city_id', $cityId)->pluck('name', 'id');  // جلب الزون المرتبطة بالمدينة
                                        })
                                        ->required()

                                        ->disabled(fn(callable $get) => !$get('city_id')),  // تعطيل الحقل إذا لم يتم اختيار مدينة
                                    TextInput::make('street_name')->required()
                                        ->label(__('Street Name')),

                                    TextInput::make('building_number')->required()
                                        ->label(__('Building Number')),

                                    TextInput::make('floor')->required()
                                        ->label(__('Floor')),

                                    Textarea::make('additional_details')->nullable()
                                        ->label(__('Additional Details')),

                                ])
                                ->required()
                                ->reactive() // لجعل الحقول تتفاعل عند تغيير قيمة الزبون
                                ->afterStateUpdated(function ($state, $set) {
                                    // عند اختيار الزبون، قم بتعيين المدينة والزون
                                    if ($state) {
                                        $customer = Customer::find($state);
                                        $set('city_id', $customer->city_id); // تعيين المدينة
                                        $set('zone_id', $customer->zone_id); // تعيين الزون
                                        $set('phone_number', $customer->phone); // تعيين الزون
                                    }
                                }),

                            Select::make('city_id')
                                ->label(__('City'))




                                ->options(City::all()->pluck('name', 'id')),
                            // , // تعيين هذا الحقل ليكون غير قابل للتعديل

                            // Select::make('zone_id')


                            //     ->label('Zone')
                            //     ->options(Zone::all()->pluck('name', 'id')),
                            TextInput::make('additional_details')->label(__('Zone'))->required(),

                            // , // تعيين هذا الحقل ليكون غير قابل للتعديل
                            Forms\Components\TextInput::make('phone_number')
                                ->label(__('Phone Number'))


                                ->tel()
                                ->required()
                                ->maxLength(15),
                            Forms\Components\TextInput::make('pickup_from')


                                ->label(__('Pickup From'))
                                ->default(auth('companies')->user()->address)
                                ->nullable(),
                            Select::make('order_type_id')
                                ->label(__('Order Type'))



                                ->relationship('orderType', 'name')->default(\App\Models\OrderType::first()->id)->required(),  // اختيار نوع الطلب







                            Textarea::make('order_description')->required()->columnSpan(2) // وصف الطلب
                                ->label(__('Order Description')),
                            TextInput::make('weight')->default(5)->required()
                                ->label(__('Weight')),
                            // الوزن
                            TextInput::make('number_of_pieces')->default(1)->required()
                                ->label(__('Number of Pieces')),
                            // عدد القطع
                            TextInput::make('invoice_number')// رقم الفاتورة
                                ->label(__('Invoice Number')),

                            TextInput::make('invoice_value') // قيمة الفاتورة
                                ->label(__('Invoice Value')),

                            TextInput::make('cash_required')->required() // قي
                                ->label(__('Cash Required')),


                        ])->columns(2),
                    ])

                    ->columnSpan(['lg' => 2]),
                Forms\Components\Group::make()
                    ->schema(
                        [
                            Section::make(__('Date'))->schema([
                                TextInput::make('company_id')
                                    ->label(__('Company'))

                                    ->default(auth('companies')->user()->id)

                                    ->readOnly(),
                                Select::make('delivery_option')


                                    ->label(__('Delivery Option')) // اسم حقل خيار التسليم
                                    ->options([
                                        'same day' => 'Same Day',
                                        'next day' => 'Next Day',
                                        'custom date' => 'Custom Date',
                                    ])
                                    ->required()
                                    ->reactive() // لجعل الحقل تفاعليًا
                                    ->afterStateUpdated(fn($set) => $set('custom_delivery_date', null)), // إعادة ضبط حقل التاريخ عند تغيير الخيار

                                DatePicker::make('custom_delivery_date')

                                    ->label(__('Custom Delivery Date')) // اسم حقل التاريخ
                                    ->required()
                                    ->native(condition: false)
                                    ->hidden(fn(callable $get) => $get('delivery_option') != 'custom date'), // إخفاء الحقل إذا لم يكن الخيار "Custom Date"
                            ]),


                            Section::make(__('Status'))->schema([
                                Select::make('order_status')
                                    ->label(__('Order Status'))


                                    ->options(OrderStatus::class)
                                    ->disabled(true) // جعل الحقل غير قابل للتعديل عند الإضافة
                                    ->default('pending pickup'), // القيمة الافتراضية
                                Textarea::make('order_notes')->nullable() // ملاحظات الطلب
                                    ->label(__('Order Notes')),



                            ]),
                        ]
                    )
                    ->columnSpan(['lg' => 1]),

            ])->columns(3);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('barcode')->sortable()  // عرض بار كود الطلب
                    ->label(__('Barcode')),

                TextColumn::make('customer.name')->label(__('Customer'))->sortable(),  // عرض اسم الزبون

                TextColumn::make('delivery_option')->sortable()// عرض خيار التسليم
                    ->label(__('Delivery Option')),

                TextColumn::make('cash_required')->sortable()
                    ->label(__('Cash Required'))

                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money()
                    ]),
                // عرض قيمة الكاش المطلوبة
                TextColumn::make('order_status')->badge() // عرض حالة الطلب
                    ->label(__('Order Status')),

                TextColumn::make('total_amount')
                    ->label(__('Amount'))

                    ->sortable(),
                TextColumn::make('del')


                    ->label(__('Delevery Price'))

                    ->sortable()





            ])
            ->filters(
                [

                    SelectFilter::make('city_id')
                        ->multiple()
                        ->label(__('City'))
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
                        ->label(__('Zone'))
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
                        ->label(__('Phone Number'))
                        ->form([
                            TextInput::make('phone_number')
                                ->label(__('Phone Number'))


                        ])
                        ->query(function (Builder $query, array $data): Builder {
                            return $query->where('phone_number', 'like', '%' . $data['phone_number'] . '%');
                        }),


                    Filter::make('created_at')
                        ->label(__('created_at'))

                        ->form([
                            DatePicker::make('created_from')
                                ->label(__('Created From')),

                            DatePicker::make('created_until')
                                ->label(__('Created Until')),

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
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                BulkAction::make('Print Labels')
                    ->label(__('Print Labels'))


                    ->action(
                        function (Collection $records, Component $livewire) {
                            $recordIds = $records->pluck('id')->toArray();
                            $ids = implode('-', $recordIds);


                            // dd($ids);
                            $livewire->js('window.open(\' ' . route('orders.prints', $ids) . ' \', \'_blank\');');
                            // return redirect()->route('orders.prints',$ids);
                        }
                    )
                    ->openUrlInNewTab(),
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),


            ]);
    }
    public static function getRelations(): array
    {
        return [
            OrderLogsRelationManager::class,
        ];
    }
    public static function getWidgets(): array
    {
        return [
            OrderOverview::class,
        ];
    }





    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }




}
