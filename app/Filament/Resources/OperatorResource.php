<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OperatorResource\Pages;
use App\Filament\Resources\OperatorResource\RelationManagers;
use App\Models\Operator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OperatorResource extends Resource
{
    protected static ?string $model = Operator::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationGroup = 'Users';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
               
                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->required(),

                Forms\Components\TextInput::make('email')
                    ->label('User Name')
                    ->required(),

                Forms\Components\TextInput::make('phone')
                    ->label('Phone')
                    ->required(),

                Forms\Components\TextInput::make('password')
                    ->label('Password')
                    ->required()
                    ->password()
                    ->minLength(8) // يمكنك تعديل طول الرقم السري حسب الحاجة
                    ->confirmed() // إذا كنت ترغب في وجود تأكيد للرقم السري
                    ->dehydrated(fn ($state) => !empty($state)), // يمنع إرسال القيمة الفارغة

                Forms\Components\TextInput::make('password_confirmation')
                    ->label('Confirm Password')
                    ->password()
                    ->dehydrated(false), // يمنع إرسال القيمة الفارغة
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
               
                Tables\Columns\TextColumn::make('name')
                    ->label('Name'),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone'),
                    BadgeColumn::make('blocked')
                    ->label('Status')
                    ->colors([
                        'danger' => 'Blocked', // اللون الأحمر عندما تكون الشركة محظورة
                        'success' => 'Active', // اللون الأخضر عندما تكون الشركة غير محظورة
                    ])
                    ->getStateUsing(fn($record) => $record->is_blocked ? 'Blocked' : 'Active'),
                // يمكنك إضافة المزيد من الأعمدة حسب الحاجة
            ])
            ->filters([
                // أضف الفلاتر إذا كنت ترغب في ذلك
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListOperators::route('/'),
            'create' => Pages\CreateOperator::route('/create'),
            'edit' => Pages\EditOperator::route('/{record}/edit'),
        ];
    }
}
