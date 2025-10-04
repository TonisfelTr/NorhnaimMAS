<?php

namespace App\Filament\Resources\BalanceTransactionResource\Pages;

use App\Filament\Resources\BalanceTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageBalanceTransactions extends ManageRecords
{
    protected static string $resource = BalanceTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
