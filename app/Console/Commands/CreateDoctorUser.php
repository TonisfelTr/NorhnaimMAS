<?php

namespace App\Console\Commands;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Throwable;

class CreateDoctorUser extends Command
{
    protected $signature = 'doctor:create';

    protected $description = 'Создать пользователя с ролью doctors и профиль врача для клиники 91';

    private const CLINIC_ID = 91;

    public function handle(): int
    {
        $login = trim((string) $this->ask('Логин'));
        $email = mb_strtolower(trim((string) $this->ask('Email')));
        $password = (string) $this->secret('Пароль');

        $validator = Validator::make(
            compact('login', 'email', 'password'),
            [
                'login' => ['required', 'string', 'max:255', 'regex:/^\S+$/u', Rule::unique('users', 'login')],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
                'password' => ['required', 'string', 'min:8'],
            ],
            [],
            [
                'login' => 'логин',
                'email' => 'email',
                'password' => 'пароль',
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        if (! Clinic::query()->whereKey(self::CLINIC_ID)->exists()) {
            $this->error('Клиника с ID ' . self::CLINIC_ID . ' не найдена.');
            return self::FAILURE;
        }

        if (! Role::query()->where('name', 'doctors')->where('guard_name', 'web')->exists()) {
            $this->error('Роль doctors с guard_name=web не найдена.');
            return self::FAILURE;
        }

        try {
            [$user, $doctor] = DB::transaction(function () use ($login, $email, $password) {
                $user = User::create([
                    'login' => $login,
                    'email' => $email,
                    'password' => $password, // В модели User применяется cast "hashed".
                    'email_verified_at' => now(),
                ]);

                $user->assignRole('doctors');

                $doctor = Doctor::create([
                    'user_id' => $user->id,
                    'clinic_id' => self::CLINIC_ID,
                    'email' => $email,
                    // Эти поля NOT NULL; персональные данные нужно заполнить позже.
                    'name' => 'Не указано',
                    'surname' => 'Не указано',
                    'birth_at' => '1900-01-01', // Техническая дата-заглушка, НЕ дата рождения.
                    'address_job' => 'Не указано',
                    'profession' => 'Врач',
                ]);

                return [$user, $doctor];
            });
        } catch (Throwable $e) {
            report($e);
            $this->error('Создание отменено из-за ошибки. Подробности — в логах Laravel.');
            return self::FAILURE;
        }

        $this->info('Пользователь и профиль врача успешно созданы.');
        $this->table(
            ['User ID', 'Doctor ID', 'Логин', 'Email', 'Роль', 'Clinic ID'],
            [[$user->id, $doctor->id, $login, $email, 'doctors', self::CLINIC_ID]]
        );
        $this->warn('Заполните настоящие ФИО, дату рождения, место работы и должность в профиле врача.');

        return self::SUCCESS;
    }
}
