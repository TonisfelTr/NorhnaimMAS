<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Jobs\BalanceTransactionJob;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function beforeSave(): void
    {
        if (!empty($this->data['password'])) {
            $this->data['password'] = Hash::make($this->data['password']);
        } else {
            unset($this->data['password']);
        }

        $originalBalance = $this->record->balance;
        $newBalance = $this->data['balance'] ?? $originalBalance;

        if ($newBalance != $originalBalance) {
            DB::transaction(function () use ($originalBalance, $newBalance) {
                DB::table('balance_transactions')->insert([
                    'user_id' => $this->record->id,
                    'reason' => "Изменение баланса администратором " . (Auth::user()->login ?? 'Admin'),
                    'old_balance' => $originalBalance,
                    'new_balance' => $newBalance,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Log::info("Транзакция баланса записана напрямую: $originalBalance -> $newBalance");
            });
        } else {
            Log::info("Баланс не изменился, транзакция не записана.");
        }
    }


    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
