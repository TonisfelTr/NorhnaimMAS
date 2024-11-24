<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BalanceTransactionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $message;
    public $userID;
    public $newBalance;

    /**
     * Create a new job instance.
     */
    public function __construct($message, $userID, $newBalance)
    {
        $this->message = $message;
        $this->userID = $userID;
        $this->newBalance = $newBalance;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::findOrFail($this->userID);
        if ($user->balance != $this->newBalance) {
            $oldBalance = $user->balance;

            DB::table('balance_transactions')->insert([
                'user_id' => $user->id,
                'reason' => $this->message,
                'old_balance' => $oldBalance,
                'new_balance' => $this->newBalance
            ]);

            $user->balance = $this->newBalance;
            if ($user->save()) {
                Log::info("Баланс пользователя {$user->id} был обновлён: {$oldBalance} -> {$this->newBalance}");
            }
        }
    }
}
