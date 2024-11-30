<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'norhnaim:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrations and seeders required for site.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Seed diagnoses to database...');
        Artisan::call('db:seed', [
            '--class' => 'DiagnosesFillSeeder',
        ]);

        $this->info('Seed clinics records by factory...');
        Artisan::call('db:seed', [
            '--class' => 'ClinicFillByFactorySeeder',
        ]);

        $this->info('Seed doctors records by factory...');
        Artisan::call('db:seed', [
            '--class' => 'DoctorFillByFactorySeeder',
        ]);

        $this->info('Make record for ID 0 to private practice...');
        Artisan::call('db:seed', [
           '--class' => 'CreateNullClinicSeeder'
        ]);

        $this->info('Seed side effects...');
        Artisan::call('db:seed', [
            '--class' => 'SideEffectsFillSeeder'
        ]);

        $this->info('Seed drugs...');
        Artisan::call('db:seed', [
            '--class' => 'DrugSeeder'
        ]);

        $this->info('Seed base groups...');
        Artisan::call('db:seed', [
            '--class' => 'CreateBaseGroupSeeder'
        ]);

        $this->info('Seed admin user...');
        Artisan::call('db:seed', [
            '--class' => 'AdminUserSeeder'
        ]);

        return Command::SUCCESS;
    }
}
