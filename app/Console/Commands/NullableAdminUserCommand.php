<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class NullableAdminUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'norhnaim:admin-account-reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset administration accounts\'s password.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Reseting...');

        Artisan::call('db:seed', [
            '--class' => 'AdminPasswordRecoverySeeder',
        ]);

        $this->info('Administrator account\'s password has been reset to "admin" successfully!');
        return Command::SUCCESS;
    }
}
