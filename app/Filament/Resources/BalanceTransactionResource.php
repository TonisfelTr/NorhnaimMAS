<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BalanceTransactionResource\Pages\ListBalanceTransactions;
use App\Models\BalanceTransaction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BalanceTransactionResource extends Resource
{
    protected static ?string $navigationIcon = null;
    protected static ?string $label = 'Транзакция баланса';
    protected static ?string $pluralLabel = 'Транзакции баланса';
    protected static bool $shouldRegisterNavigation = false;

    public static function table(Table $table): Table
    {
        return $table
            ->query(BalanceTransaction::query())
            ->columns([
                Tables\Columns\TextColumn::make('reason')->label('Причина'),
                Tables\Columns\TextColumn::make('old_balance')->label('Старый баланс')->money('RUB'),
                Tables\Columns\TextColumn::make('new_balance')->label('Новый баланс')->money('RUB'),
                Tables\Columns\TextColumn::make('created_at')->label('Дата')->dateTime('d.m.Y H:i:s'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Пользователь')
                    ->relationship('user', 'login')
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBalanceTransactions::route('/'),
        ];
    }
}
