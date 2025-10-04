<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TopicsCategoryResource\Pages;
use App\Models\TopicsCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TopicsCategoryResource extends Resource
{
    protected static ?string $model = TopicsCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';
    protected static ?string $navigationLabel = 'Категории';
    protected static ?string $navigationGroup = 'Контент';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Название')
                ->required()
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Название')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('Создано')->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')->label('Обновлено')->dateTime(),
            ])
            ->filters([
                // Здесь можно добавить фильтры
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->authorize(fn ($record) => auth()->user()->can('blog_remove_category')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->authorize(fn () => auth()->user()->can('blog_remove_category')),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTopicsCategories::route('/'),
            'create' => Pages\CreateTopicsCategory::route('/create'),
            'edit' => Pages\EditTopicsCategory::route('/{record}/edit'),
        ];
    }
}
