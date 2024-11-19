<?php

namespace App\Filament\Company\Resources;

use App\Enums\OrderStatus;
use App\Events\OrderUpdated;
use App\Filament\Company\Resources\OrderResource\Pages;
use App\Filament\Company\Resources\OrderResource\Pages\CreateOrder;
use App\Filament\Company\Resources\OrderResource\Pages\EditOrder;
use App\Filament\Company\Resources\OrderResource\Pages\ListOrders;
use App\Filament\Company\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\OrderResource\RelationManagers\OrderLogsRelationManager;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use Str;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';



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
                                ->label('Barcode')->columnSpan(2), // تعيين اسم الحقل/ توليد بار كود تلقائي

                            Forms\Components\Select::make('customer_id')
                                ->relationship('customer', 'name')
                                ->searchable()
                                ->preload()

                                ->createOptionForm([

                                    TextInput::make('name')->required(),
                                    TextInput::make('phone')->required(),
                                    TextInput::make('company_id')
                                        ->default(auth('companies')->user()->id)
                                        ->readOnly(),
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


                                ->label('City')
                                ->options(City::all()->pluck('name', 'id')),
                            // , // تعيين هذا الحقل ليكون غير قابل للتعديل

                            // Select::make('zone_id')


                            //     ->label('Zone')
                            //     ->options(Zone::all()->pluck('name', 'id')),
                            TextInput::make('additional_details')->label('Zone')->required(),

                            // , // تعيين هذا الحقل ليكون غير قابل للتعديل
                            Forms\Components\TextInput::make('phone_number')
                                ->label('Phone Number')


                                ->tel()
                                ->required()
                                ->maxLength(15),
                            Forms\Components\TextInput::make('pickup_from')


                                ->label('Pickup From')
                                ->default(auth('companies')->user()->address)
                                ->nullable(),
                            Select::make('order_type_id')


                                ->relationship('orderType', 'name')->default(\App\Models\OrderType::first()->id)->required(),  // اختيار نوع الطلب







                            Textarea::make('order_description')->required()->columnSpan(2), // وصف الطلب
                            TextInput::make('weight')->default(5)->required(), // الوزن
                            TextInput::make('number_of_pieces')->default(1)->required(), // عدد القطع
                            TextInput::make('invoice_number'), // رقم الفاتورة
                            TextInput::make('invoice_value'), // قيمة الفاتورة
                            TextInput::make('cash_required')->required(), // قيمة الكاش المطلوبة


                        ])->columns(2),
                    ])

                    ->columnSpan(['lg' => 2]),
                Forms\Components\Group::make()
                    ->schema(
                        [
                            Section::make('Date')->schema([
                                TextInput::make('company_id')
                                    ->default(auth('companies')->user()->id)

                                    ->readOnly(),
                                Select::make('delivery_option')

                                    ->label('Delivery Option') // اسم حقل خيار التسليم
                                    ->options([
                                        'same day' => 'Same Day',
                                        'next day' => 'Next Day',
                                        'custom date' => 'Custom Date',
                                    ])
                                    ->required()
                                    ->reactive() // لجعل الحقل تفاعليًا
                                    ->afterStateUpdated(fn($set) => $set('custom_delivery_date', null)), // إعادة ضبط حقل التاريخ عند تغيير الخيار

                                DatePicker::make('custom_delivery_date')

                                    ->label('Custom Delivery Date') // اسم حقل التاريخ
                                    ->required()
                                    ->native(condition: false)
                                    ->hidden(fn(callable $get) => $get('delivery_option') != 'custom date'), // إخفاء الحقل إذا لم يكن الخيار "Custom Date"
                            ]),


                            Section::make('Status')->schema([
                                Select::make('order_status')

                                    ->options(OrderStatus::class)
                                    ->disabled(true) // جعل الحقل غير قابل للتعديل عند الإضافة
                                    ->default('pending pickup'), // القيمة الافتراضية
                                Textarea::make('order_notes')->nullable(), // ملاحظات الطلب


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
                    ->label('Amount')

                    ->sortable(),
                TextColumn::make('del')
                    ->label(' Delevery Price')

                    ->sortable()





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

            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                BulkAction::make('Print Labels')

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
