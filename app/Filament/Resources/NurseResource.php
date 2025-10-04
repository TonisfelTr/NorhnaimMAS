<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NurseResource\Pages;
use App\Models\Nurse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NurseResource extends Resource
{
    protected static ?string $model = Nurse::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Пользователи';

    protected static ?string $navigationLabel = 'Медсестры';

    protected static ?string $modelLabel = 'медсестру';

    protected static ?string $pluralModelLabel = 'медсестры';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Личные данные')
                    ->schema([
                        Forms\Components\TextInput::make('surname')
                            ->label('Фамилия')
                            ->required()
                            ->maxLength(50),
                        Forms\Components\TextInput::make('name')
                            ->label('Имя')
                            ->required()
                            ->maxLength(50),
                        Forms\Components\TextInput::make('patronym')
                            ->label('Отчество')
                            ->maxLength(50),
                    ])->columns(3),

                Forms\Components\Section::make('Профессиональная информация')
                    ->schema([
                        Forms\Components\Select::make('clinic_id')
                            ->label('Клиника')
                            ->relationship('clinic', 'name')
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('user_id')
                            ->label('Пользователь')
                            ->relationship('user', 'email', modifyQueryUsing: fn (Builder $query) => $query
                                ->whereDoesntHave('nurse'))
                            ->optionsLimit(5)
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('profession')
                            ->label('Профессия')
                            ->required()
                            ->maxLength(100),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('surname')
                    ->label('Фамилия')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Имя')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('patronym')
                    ->label('Отчество')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('clinic.name')
                    ->label('Клиника')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Пользователь')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('profession')
                    ->label('Профессия')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // возможные фильтры
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNurses::route('/'),
            'create' => Pages\CreateNurse::route('/create'),
            'edit' => Pages\EditNurse::route('/{record}/edit'),
        ];
    }
}
