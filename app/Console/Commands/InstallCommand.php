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
        $this->warn('Seed diagnoses to database...');
        Artisan::call('db:seed', [
            '--class' => 'DiagnosesFillSeeder',
        ]);

        $this->warn('Seed clinics records by factory...');
        Artisan::call('db:seed', [
            '--class' => 'ClinicFillByFactorySeeder',
        ]);

        $this->warn('Seed doctors records by factory...');
        Artisan::call('db:seed', [
            '--class' => 'DoctorFillByFactorySeeder',
        ]);

        $this->warn('Make record for ID 0 to private practice...');
        Artisan::call('db:seed', [
           '--class' => 'CreateNullClinicSeeder'
        ]);

        $this->warn('Seed side effects...');
        Artisan::call('db:seed', [
            '--class' => 'SideEffectsFillSeeder'
        ]);

        $this->warn('Seed contraindications...');
        Artisan::call('db:seed', [
            '--class' => 'ContraindicationsSeeder'
        ]);

        $this->warn('Seed drugs...');
        Artisan::call('db:seed', [
            '--class' => 'DrugSeeder'
        ]);

        $this->warn('Seed lawyers...');
        Artisan::call('db:seed', [
            '--class' => 'LawyersFillSeeder'
        ]);

        $this->warn('Seed base groups...');
        Artisan::call('db:seed', [
            '--class' => 'CreateBaseGroupSeeder'
        ]);

        $this->warn('Seed admin user...');
        Artisan::call('db:seed', [
            '--class' => 'AdminUserSeeder'
        ]);

        $this->warn('Creating "cache" table...');
        Artisan::call('cache:table');
        Artisan::call('migrate');

        $this->warn('Seed settings...');
        Artisan::call('db:seed', [
           '--class' => 'SettingsItemsSeeder'
        ]);

        $this->info('Install has been completed successfully.');
        return Command::SUCCESS;
    }
}
