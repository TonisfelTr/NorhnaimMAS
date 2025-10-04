<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannedResource\Pages;
use App\Models\Banned;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class BannedResource extends Resource
{
    protected static ?string $model = Banned::class;
    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';
    protected static ?string $navigationLabel = 'Блокировки';
    protected static ?string $navigationGroup = 'Общие настройки';
    protected static ?string $pluralLabel = 'Блокировки';
    protected static ?string $modelLabel = 'блокировку';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->label('Пользователь')
                ->relationship('user', 'login')
                ->searchable()
                ->required(),

            Forms\Components\Select::make('rule_id')
                ->label('Причина блокировки')
                ->relationship('rule', 'text')
                ->required(),

            Forms\Components\DatePicker::make('from')
                ->label('Дата начала блокировки')
                ->default(Carbon::today())
                ->required(),

            Forms\Components\DatePicker::make('to')
                ->label('Дата окончания блокировки'),

            Forms\Components\Hidden::make('admin_id')
                ->default(auth()->id()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.login')
                    ->label('Пользователь')
                    ->sortable()->searchable(),

                Tables\Columns\TextColumn::make('rule.text')
                    ->label('Причина')
                    ->sortable()->searchable(),

                Tables\Columns\TextColumn::make('admin.login')
                    ->label('Заблокировал')
                    ->sortable(),

                Tables\Columns\TextColumn::make('from')
                    ->label('С')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable(),

                Tables\Columns\TextColumn::make('to')
                    ->label('До')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m.Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rule_id')
                    ->relationship('rule', 'text')
                    ->label('Причина'),

                Tables\Filters\SelectFilter::make('admin_id')
                    ->relationship('admin', 'login')
                    ->label('Администратор'),
            ])
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
            'index' => Pages\ListBanneds::route('/'),
            'create' => Pages\CreateBanned::route('/create'),
            'edit' => Pages\EditBanned::route('/{record}/edit'),
        ];
    }
}
