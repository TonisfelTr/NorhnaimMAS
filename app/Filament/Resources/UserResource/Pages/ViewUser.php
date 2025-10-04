<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables;
use Illuminate\Support\Facades\DB;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getTableQuery(): array
    {
        return DB::table('balance_transactions')
            ->where('user_id', $this->record->id)
            ->orderByDesc('created_at')
            ->get()
            ->toArray();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('reason')->label('Причина'),
            Tables\Columns\TextColumn::make('old_balance')->label('Старый баланс')->money('RUB'),
            Tables\Columns\TextColumn::make('new_balance')->label('Новый баланс')->money('RUB'),
            Tables\Columns\TextColumn::make('created_at')->label('Дата транзакции')->dateTime(),
        ];
    }
}
