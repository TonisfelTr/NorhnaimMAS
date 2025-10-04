<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TopicResource\Pages;
use App\Models\Topic;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Auth;

class TopicResource extends Resource
{
    protected static ?string $model = Topic::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Темы';
    protected static ?string $navigationGroup = 'Контент';

    protected static ?string $modelLabel = 'тему';

    protected static ?string $pluralModelLabel = 'темы';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->label('Название')->required(),
                Forms\Components\Select::make('topics_category_id')->relationship('topics_category', 'name')->label('Категория')->required(),
                Forms\Components\Textarea::make('description')->label('Описание')->required()->columnSpanFull(),
                Forms\Components\RichEditor::make('content')->label('Содержание')->required()->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Название')->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('Автор')->searchable(),
                Tables\Columns\TextColumn::make('topics_category.name')->label('Категория'),
                Tables\Columns\TextColumn::make('created_at')->label('Создано')->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')->label('Обновлено')->dateTime(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTopics::route('/'),
            'create' => Pages\CreateTopic::route('/create'),
            'edit' => Pages\EditTopic::route('/{record}/edit'),
        ];
    }

    public static function beforeCreate($record, $data): void
    {
        $record->user_id = Auth::id();
    }
}
