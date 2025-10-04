<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClinicResource\Pages;
use App\Models\Clinic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;

class ClinicResource extends Resource
{
    protected static ?string $model = Clinic::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $navigationLabel = 'Клиники';
    protected static ?string $pluralLabel = 'Клиники';
    protected static ?string $modelLabel = 'Клиника';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Основные данные')
                ->schema([
                    Forms\Components\TextInput::make('name')->required()->label('Название'),
                    Forms\Components\Textarea::make('description')->label('Описание'),
                    Forms\Components\TextInput::make('address')->required()->label('Адрес'),
                    Forms\Components\TextInput::make('phone')->label('Телефон'),
                    Forms\Components\TextInput::make('city')->label('Город'),
                ])->columns(2),

            Forms\Components\Section::make('Услуги клиники')
                ->schema([
                    Forms\Components\Repeater::make('services')
                        ->label('Услуги')
                        ->relationship()
                        ->schema([
                            Forms\Components\TextInput::make('name')->required()->label('Название услуги'),
                        ])
                        ->defaultItems(0),
                ]),

            Forms\Components\Section::make('Фотографии клиники')
                ->schema([
                    Forms\Components\SpatieMediaLibraryFileUpload::make('photos')
                        ->collection('photos')
                        ->multiple()
                        ->image()
                        ->label('Фотографии'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('photos')
                    ->collection('photos')
                    ->circular()
                    ->label('Фото'),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable()->label('Название'),
                Tables\Columns\TextColumn::make('address')->searchable()->label('Адрес'),
                Tables\Columns\TextColumn::make('phone')->searchable()->label('Телефон'),
                Tables\Columns\TextColumn::make('city')->searchable()->sortable()->label('Город'),
                Tables\Columns\TextColumn::make('created_at')->date('d.m.Y')->sortable()->label('Дата добавления'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('city')
                    ->options(Clinic::query()->distinct()->pluck('city', 'city'))
                    ->label('Город'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Редактировать'),
                Tables\Actions\DeleteAction::make()->label('Удалить'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Удалить выбранные'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClinics::route('/'),
            'create' => Pages\CreateClinic::route('/create'),
            'edit' => Pages\EditClinic::route('/{record}/edit'),
        ];
    }
}
