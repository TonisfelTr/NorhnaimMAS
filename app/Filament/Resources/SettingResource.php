<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?string $navigationGroup = 'Общие настройки';
    protected static ?string $navigationLabel = 'Настройки';

    protected static ?string $modelLabel = 'настройку';

    protected static ?string $pluralModelLabel = 'настройки';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->disabled()
                    ->label('Ключ'),

                Forms\Components\TextInput::make('value')
                    ->label('Значение')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')->label('Ключ')->searchable(),
                Tables\Columns\TextColumn::make('value')->label('Значение'),
                Tables\Columns\TextColumn::make('updated_at')->label('Обновлено')->dateTime(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
