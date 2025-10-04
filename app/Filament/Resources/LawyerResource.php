<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LawyerResource\Pages;
use App\Models\Lawyer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LawyerResource extends Resource
{
    protected static ?string $model = Lawyer::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationLabel = 'Юристы';
    protected static ?string $navigationGroup = 'Пользователи';
    protected static ?string $modelLabel = 'юрист';
    protected static ?string $pluralModelLabel = 'юристы';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Персональные данные')->schema([
                Forms\Components\TextInput::make('name')->label('Имя')->required()->maxLength(255),
                Forms\Components\TextInput::make('surname')->label('Фамилия')->required()->maxLength(255),
                Forms\Components\TextInput::make('profession')->label('Профессия')->required()->maxLength(255),
                Forms\Components\TextInput::make('phone')->label('Телефон')->required()->maxLength(255),
            ])->columns(2),

            Forms\Components\Section::make('Профессиональные данные')->schema([
                Forms\Components\TextInput::make('base_price')->label('Стоимость услуг')->required()->numeric(),
                Forms\Components\TextInput::make('experience')->label('Опыт (лет)')->required()->numeric(),
                Forms\Components\TagsInput::make('skills')
                    ->label('Навыки')
                    ->separator(',')
                    ->placeholder('Введите навык и нажмите Enter'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('surname')->label('Фамилия')->searchable(),
                Tables\Columns\TextColumn::make('name')->label('Имя')->searchable(),
                Tables\Columns\TextColumn::make('profession')->label('Профессия')->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('Телефон')->searchable(),
                Tables\Columns\TextColumn::make('base_price')->label('Стоимость')->sortable(),
                Tables\Columns\TextColumn::make('experience')->label('Опыт (лет)')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Создано')->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')->label('Обновлено')->dateTime(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->authorize(fn ($record) => auth()->user()->can('lawyer_remove')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->authorize(fn () => auth()->user()->can('lawyer_remove')),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLawyers::route('/'),
            'create' => Pages\CreateLawyer::route('/create'),
            'edit' => Pages\EditLawyer::route('/{record}/edit'),
        ];
    }
}
