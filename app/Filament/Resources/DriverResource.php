<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DriverResource\Pages;
use App\Filament\Resources\DriverResource\RelationManagers;
use App\Models\Driver;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DriverResource extends Resource
{
    protected static ?string $model = Driver::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Users';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Driver Name')
                    ->required(),

                TextInput::make('car_type')
                    ->label('Car Type')
                    ->required(),

                Forms\Components\TextInput::make('car_number')
                    ->label('Car Number')
                    ->required(),

                FileUpload::make('image')
                    ->label('Driver Image')
                    ->image()
                    ->directory('drivers'), // تحديد مسار رفع الصور
                TextInput::make('phone_number')
                    ->label('Phone Number')
                    ->required(),
                TextInput::make('email')
                    ->label('Email')
                    ->email() // التحقق من صيغة البريد الإلكتروني
                    ->required()
                    ->unique(ignoreRecord: true), // جعله فريدًا

                TextInput::make('password')
                    ->label('Password')
                    ->password() // لجعل الحقل خاص بكلمة المرور
                    ->dehydrateStateUsing(fn($state) => bcrypt($state)), // تشفير كلمة المرور

                Forms\Components\TextInput::make('price_per_order')
                    ->label('Price per Order')
                    ->numeric()
                    ->required(),
                Forms\Components\Toggle::make('blocked')
                    ->label('Blocked')
                    ->inlineLabel()
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Driver Name')->searchable(),
                TextColumn::make('car_type')->label('Car Type')->searchable(),
                ImageColumn::make('image')->label('Image'),
                TextColumn::make('phone_number')->label('Phone Number')->searchable(),
                BadgeColumn::make('blocked')
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDrivers::route('/'),
            'create' => Pages\CreateDriver::route('/create'),
            'edit' => Pages\EditDriver::route('/{record}/edit'),
        ];
    }
}
