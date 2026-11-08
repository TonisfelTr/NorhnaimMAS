<?php

namespace App\Console\Commands;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Throwable;

class CreateLaborantUser extends Command
{
    protected $signature = 'laborant:create {--clinic= : ID клиники, к которой привязать лаборанта}';

    protected $description = 'Создать пользователя с ролью laborant и привязать его к клинике';

    // В исходной команде врача была клиника 91. Оставляем её как значение по умолчанию,
    // но разрешаем указать другую клинику в консоли или опцией --clinic=.
    private const DEFAULT_CLINIC_ID = '91';

    public function handle(): int
    {
        if (! Schema::hasColumn('users', 'laboratory_clinic_id')) {
            $this->error('Отсутствует users.laboratory_clinic_id. Сначала установите миграцию кабинета лаборанта: php artisan migrate.');

            return self::FAILURE;
        }

        $rawClinicId = $this->option('clinic');

        if ($rawClinicId === null) {
            $rawClinicId = $this->ask('ID клиники', self::DEFAULT_CLINIC_ID);
        }

        $clinicIdInput = trim((string) $rawClinicId);
        $login = trim((string) $this->ask('Логин'));
        $email = mb_strtolower(trim((string) $this->ask('Email')));
        $password = (string) $this->secret('Пароль');

        $validator = Validator::make(
            [
                'clinic_id' => $clinicIdInput,
                'login' => $login,
                'email' => $email,
                'password' => $password,
            ],
            [
                'clinic_id' => ['required', 'integer', 'min:1', Rule::exists('clinics', 'id')],
                'login' => ['required', 'string', 'max:255', 'regex:/^\S+$/u', Rule::unique('users', 'login')],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
                'password' => ['required', 'string', 'min:8'],
            ],
            [],
            [
                'clinic_id' => 'клиника',
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

        $clinicId = (int) $clinicIdInput;
        $clinic = Clinic::query()->find($clinicId);

        if (! $clinic) {
            $this->error('Клиника с ID ' . $clinicId . ' не найдена.');

            return self::FAILURE;
        }

        try {
            $user = DB::transaction(function () use ($login, $email, $password, $clinicId): User {
                // Создаём роль автоматически, если она ещё не была добавлена в систему.
                $role = Role::findOrCreate('laborant', 'web');

                // Не создаём профиль Doctor: лабораторный сотрудник — это только User.
                $user = new User();
                $user->login = $login;
                $user->email = $email;
                $user->password = Hash::make($password);
                $user->email_verified_at = now();
                $user->laboratory_clinic_id = $clinicId;
                $user->save();

                $user->assignRole($role);

                return $user;
            });
        } catch (Throwable $e) {
            report($e);
            $this->error('Создание лаборанта отменено из-за ошибки. Подробности — в логах Laravel.');

            return self::FAILURE;
        }

        $this->info('Учётная запись лаборанта успешно создана.');
        $this->table(
            ['User ID', 'Логин', 'Email', 'Роль', 'Clinic ID', 'Клиника'],
            [[$user->id, $login, $email, 'laborant', $clinicId, $clinic->name ?? '—']]
        );
        $this->line('Вход: ' . url('/laboratory/login'));

        return self::SUCCESS;
    }
}
