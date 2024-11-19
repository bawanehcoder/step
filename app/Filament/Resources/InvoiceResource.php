<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Filament\Resources\InvoiceResource\Widgets\PaidReceivedInvoicesWidget;
use App\Models\Company;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MultiSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Financial';

    protected static ?string $navigationLabel = 'Receipts';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    // الخطوة 1: اختيار الطلبات ونوع الفاتورة
                    Wizard\Step::make('Orders & Invoice Type')
                        ->schema([
                            MultiSelect::make('orders')
                                ->label('Orders')
                                ->relationship('orders', 'barcode')
                                ->options(Order::all()->pluck('barcode', 'id'))
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(fn(callable $set) => $set('amount', null)), // إعادة تعيين المبلغ عند تغيير الطلبات

                            Select::make('type')
                                ->label('Invoice Type')
                                ->options([
                                    'issued' => 'Issued',
                                    'received' => 'Received',
                                ])
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(fn(callable $set) => $set('amount', null)), // إعادة تعيين المبلغ عند تغيير نوع الفاتورة
                        ]),

                    // الخطوة 2: اختيار المستفيد والتفاصيل
                    Wizard\Step::make('Beneficiary & Details')
                        ->schema([
                            Select::make('beneficiary_type')
                                ->label('Beneficiary Type')
                                ->options([
                                    'company' => 'Company',
                                    'driver' => 'Driver',
                                ])
                                ->reactive()
                                ->required()
                                ->afterStateUpdated(fn(callable $set) => $set('amount', null)), // إعادة تعيين المبلغ عند تغيير نوع المستفيد

                            Select::make('beneficiary_id')
                                ->label('Beneficiary')
                                ->options(function (callable $get) {
                                    if ($get('beneficiary_type') === 'company') {
                                        return Company::pluck('name', 'id');
                                    } elseif ($get('beneficiary_type') === 'driver') {
                                        return Driver::pluck('name', 'id');
                                    }
                                    return [];
                                })
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($set, callable $get) {
                                    $orders = Order::find($get('orders'));
                                    // الحصول على الطلبات المختارة
                                    $beneficiaryType = $get('beneficiary_type');
                                    $invoiceType = $get('type');
                                    $total = 0;

                                    // if($beneficiaryType === 'company'){
                                    //     dd($orders);
                                    // }
                        
                                    // الحساب حسب نوع المستفيد والفاتورة
                                    if ($beneficiaryType === 'driver') {
                                        if ($invoiceType === 'received') {
                                            // إذا كان سائق ومستلم، يكون المجموع عبارة عن مجموع total_amount لكل الاوردارت
                                            $total = $orders->sum('total_amount');
                                        } elseif ($invoiceType === 'issued') {
                                            // إذا كان سائق ومرسل، يكون المجموع عبارة عن عدد الاوردرات ضرب سعر السائق
                                            $driver = Driver::find($get('beneficiary_id'));
                                            if ($driver) {
                                                $total = $orders->count() * $driver->price_per_order;
                                            }
                                        }
                                    } elseif ($beneficiaryType === 'company' && $invoiceType === 'issued') {
                                        // إذا كان شركة ومرسل، يكون المجموع عبارة عن مجموع cash_required لكل الاوردارت
                                        $total = $orders->sum('cash_required');
                                    }

                                    // تعيين القيمة المحسوبة للحقل
                                    $set('amount', $total);
                                }), // إعادة تعيين المبلغ عند تغيير المستفيد



                            Forms\Components\Textarea::make('additional_notes')
                                ->label('Additional Notes')
                                ->nullable(),

                            Textarea::make('description')
                                ->label('Description')
                                ->nullable(),

                            DatePicker::make('invoice_date')
                                ->label('Invoice Date')
                                ->default(now())
                                ->required(),
                        ]),

                    // الخطوة 3: حساب الإجمالي بناءً على نوع الفاتورة والمستفيد
                    Wizard\Step::make('Calculate Total')
                        ->schema([
                            TextInput::make('amount')
                                ->label('Total Amount')
                                ->numeric()
                                ->required()
                                // ->disabled() // إيقاف إدخال المستخدم
                                ->reactive(),
                            Forms\Components\Select::make('status')
                                ->label('Invoice Status')
                                ->options([
                                    'paid' => 'Paid',
                                    'unpaid' => 'Unpaid',
                                ])
                                ->default('unpaid')
                                ->required(),
                        ]),

                ])
                    ->columns(1) // لضبط عدد الأعمدة ليكون عموداً واحداً
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')->label('Type'),
                Tables\Columns\TextColumn::make('beneficiary_type')
                    ->label('Beneficiary Type')
                    ->sortable(),

                Tables\Columns\TextColumn::make('beneficiary_id')
                    ->label('Beneficiary Name')
                    ->formatStateUsing(function ($record) {
                        if ($record->beneficiary_type === 'company') {
                            return Company::find($record->beneficiary_id)?->name;
                        } elseif ($record->beneficiary_type === 'driver') {
                            return Driver::find($record->beneficiary_id)?->name;
                        }
                        return '-';
                    })
                    ->sortable(),

                // Tables\Columns\TextColumn::make('status')
                //     ->label('Invoice Status')
                //     ->sortable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'danger' => 'unpaid', // اللون الأحمر عندما تكون الشركة محظورة
                        'success' => 'paid', // اللون الأخضر عندما تكون الشركة غير محظورة
                    ])
                    ->getStateUsing(fn($record) => $record->status),
                TextColumn::make('invoice_date')->label('Date')->date(),
                TextColumn::make('amount')->label('Amount')->summarize([
                    Tables\Columns\Summarizers\Sum::make()
                        ->money(),
                ]),
                TextColumn::make('orders_count')->label('Orders')->counts('orders'),
            ])
            ->filters([
                SelectFilter::make('type')->label('Invoice Type')->options([
                    'issued' => 'Issued',
                    'received' => 'Received',
                ]),
                // فلتر حالة الفاتورة
                SelectFilter::make('status')
                    ->label('Invoice Status')
                    ->options([
                        'paid' => 'Paid',
                        'unpaid' => 'Unpaid',
                    ]),

                // فلتر نوع المستفيد
                SelectFilter::make('beneficiary_type')
                    ->label('Beneficiary Type')
                    ->options([
                        'company' => 'Company',
                        'driver' => 'Driver',
                    ]),


                // فلتر اسم المستفيد
                // حقل السائقين
                SelectFilter::make('driver_id')
                    ->label('Driver')
                    ->options(Driver::pluck('name', 'id'))
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            $query->where('beneficiary_id', $data['value'])->where('beneficiary_type','driver');
                        }
                    }),
                // حقل الشركات
                SelectFilter::make('company_id')
                    ->label('Company')
                    ->options(Company::pluck('name', 'id'))
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            $query->where('beneficiary_id', $data['value'])->where('beneficiary_type','company');
                        }
                    }),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                    ])->columnSpan(1)->columns(2)
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
            ], layout: FiltersLayout::AboveContent)
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
