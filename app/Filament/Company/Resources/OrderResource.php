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
                            Select::make('city_id')
                                ->label('City')
                                ->options(City::all()->pluck('name', 'id')),

                            
                            TextInput::make('additional_details')->label('Zone')->required(),
                            TextInput::make('company_name')->label('Company')->required(),
                            TextInput::make('customer_name')->label('Contact Person')->required(),


                            // ->disabled(), // تعيين هذا الحقل ليكون غير قابل للتعديل
                            Forms\Components\TextInput::make('phone_number')
                                ->label('Phone Number')
                                ->tel()
                                ->required()
                                ->maxLength(15),
                            TextInput::make('phone2')->label('Secoundry Phone'),

                            Forms\Components\TextInput::make('pickup_from')
                                ->label('Free Address')
                                ->nullable(),
                            Select::make('order_type_id')
                                ->relationship('orderType', 'name')->default(\App\Models\OrderType::first()->id),  // اختيار نوع الطلب





                                Textarea::make('order_notes')->nullable()->columnSpan(2), // ملاحظات الطلب


                            // Textarea::make('order_description')->required()->columnSpan(2), // وصف الطلب
                            TextInput::make('weight')->default(5), // الوزن
                            TextInput::make('number_of_pieces')->label('PCS')->default(1)->required(), // عدد القطع
                            // TextInput::make('invoice_number'), // رقم الفاتورة
                            // TextInput::make('invoice_value'), // قيمة الفاتورة
                            TextInput::make('cash_required')->label('Collection')->required()
                            ->disabled(fn (string $context) => $context === 'edit'), // قيمة الكاش المطلوبة

                            TextInput::make('barcode')
                            ->required()
                            ->minLength(7)
                            ->maxLength(7) // التأكد من أن الطول 12 خانة
                            ->default(function () {
                                $max_id = \DB::table('orders')->find(\DB::table('orders')->max('id'))?->barcode;
                                if ($max_id) {
                                    return (string) $max_id + 1;

                                } else {
                                    return '2700000';

                                }
                            })
                            ->disabled(fn($get) => $get('id') != null)
                            ->label('Barcode')
                            ->unique()
                            ->columnSpan(2), // تعيين اسم الحقل/ توليد بار كود تلقائي

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
