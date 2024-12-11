<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Events\OrderUpdated;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\OrderResource\RelationManagers\OrderLogsRelationManager;
use App\Filament\Resources\OrderResource\Widgets\OrderOverview;
use App\Models\City;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Invoice;
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
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Component;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Str;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Orders';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Section::make()->schema([
                            Select::make('city_id')
                            ->searchable()
                ->preload()
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
                            ->searchable()
                ->preload()
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
                            ->unique()

                            ->label('Barcode')->columnSpan(2), // تعيين اسم الحقل/ توليد بار كود تلقائي

                        ])->columns(2),
                    ])

                    ->columnSpan(['lg' => 2]),
                Forms\Components\Group::make()
                    ->schema(
                        [
                            Section::make('Date')->schema([
                                Select::make('company_id')
                                ->searchable()
                ->preload()
                                    ->label('Company')
                                    ->relationship('company', 'name') // اختيار الشركة
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set) {
                                        // dd($state);
                                        // عند اختيار الزبون، قم بتعيين المدينة والزون
                                        if ($state) {
                                            $customer = Company::find($state);

                                            $set('pickup_from', $customer->address); // تعيين الزون
                                        }
                                    }),
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
                                    ->disabled(fn($get) => $get('id') === null) // جعل الحقل غير قابل للتعديل عند الإضافة
                                    ->default('pending pickup'), // القيمة الافتراضية


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
                TextColumn::make('barcode')->sortable()->searchable(),  // عرض بار كود الطلب
                ImageColumn::make('barcode_image'),


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
                    ->label(' Amount')

                    ->sortable(),

                TextColumn::make('del')
                    ->label(' Delevery Price')

                    ->sortable(),





            ])
            ->filters(
                [

                    // SelectFilter::make('city_id')
                    //     ->multiple()
                    //     ->label('City')
                    //     ->options(function () {
                    //         return City::all()->pluck('name', 'id');
                    //     })
                    //     ->searchable()
                    //     ->query(function (Builder $query, array $data) {
                    //         // dd($data['values']);
                    //         if (isset($data['values'])) {
                    //             // dd($query->whereIn('city_id', $data['values']));
                    //             $query->whereIn('city_id', $data['values']);
                    //         }
                    //     }),

                    SelectFilter::make('city')
                        ->relationship('city', 'name')
                        ->multiple()

                        ->searchable()
                        ->preload(),

                    SelectFilter::make('Zone')
                        ->relationship('Zone', 'name')
                        ->multiple()

                        ->searchable()
                        ->preload(),

                    // SelectFilter::make('zone_id')
                    //     ->multiple()
                    //     ->label('Zone')
                    //     ->options(function () {
                    //         return Zone::all()->pluck('name', 'id');
                    //     })
                    //     ->searchable()
                    //     ->query(function (Builder $query, array $data) {
                    //         if (isset($data['values'])) {

                    //             // $query->orWhereIn('zone_id', $data['values']);
                    //         }
                    //     }),

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
                Tables\Actions\EditAction::make(),


            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),

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

                BulkAction::make('assignToDriver')
                    ->label('Assign to Driver')
                    ->action(function (Collection $records, array $data) {
                        // اختر السائق ثم قم بتحديث الطلبات المحددة
                        $driverId = $data['driver_id'];
                        // dd($driverId);
                        foreach ($records as $record) {
                            $record->update(['driver_id' => $driverId]);
                        }
                    })
                    ->form([
                        Select::make('driver_id')
                            ->label('Select Driver')
                            ->options(Driver::all()->pluck('name', 'id'))
                            ->required(),
                    ])
                    ->requiresConfirmation(),
                ExportBulkAction::make(),
                // BulkAction::make('Generate Delivery Sheet')
                //     // ->action(function (Collection $records) {
                //     //     return redirect()->route('filament.delivery-sheet', [
                //     //        ,
                //     //     ]);
                //     // })
                //     ->action(function (Collection $records, \Livewire\Component $livewire): void {
                //         $livewire->redirectRoute(name: 'filament.delivery-sheet', parameters: [
                //             'order_ids' => $records->pluck('id')->toArray()
                //         ]);
                //     })
                //     // ->requiresConfirmation()
                //     ->color('primary'),





                // BulkAction::make('create_invoice')
                //     ->label('Create Invoice')
                //     ->action(function (Collection $records, array $data) {

                //         $invoice = Invoice::create([
                //             'invoice_date' => now(),
                //             'beneficiary_name' => $data['beneficiary_name'],
                //             'type' => $data['type'],
                //             'description' => $data['description'],
                //             'amount' => $data['amount'],
                //         ]);

                //         $invoice->orders()->attach($records);

                //         Notification::make()->success()->title('success!')->icon('heroicon-o-check')->send();
                //     })
                //     ->form([
                //         Select::make('type')
                //             ->label('Invoice Type')
                //             ->options([
                //                 'issued' => 'Issued',
                //                 'received' => 'Received',
                //             ])
                //             ->required(),

                //         TextInput::make('beneficiary_name')
                //             ->label('Beneficiary Name')
                //             ->required(),

                //         Textarea::make('description')
                //             ->label('Description')
                //             ->nullable(),



                //         Forms\Components\TextInput::make('amount')
                //             ->required()
                //             ->numeric(),
                //     ])
                //     ->color('success')
                //     ->requiresConfirmation(),
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        $modelClass = static::$model;

        return (string) $modelClass::all()->count();
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }


}
