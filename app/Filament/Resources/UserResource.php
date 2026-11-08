<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Jobs\BalanceTransactionJob;
use App\Models\User;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Log;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Пользователи';

    protected static ?string $navigationGroup = 'Пользователи';

    protected static ?string $modelLabel = 'пользователя';

    protected static ?string $pluralModelLabel = 'пользователи';

    /**
     * Filament stores the "roles" relationship field as role IDs, not names.
     */
    public static function hasSelectedDoctorRole(mixed $roles): bool
    {
        $ids = $roles instanceof \Illuminate\Support\Collection
            ? $roles->all()
            : (is_array($roles) ? $roles : [$roles]);

        $ids = array_filter($ids, static fn (mixed $id): bool => is_numeric($id));

        return $ids !== []
            && \Spatie\Permission\Models\Role::query()
                ->where('name', 'doctors')
                ->whereIn('id', $ids)
                ->exists();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Основная информация')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('login')
                            ->label('Логин')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('password')
                            ->label('Пароль')
                            ->password()
                            ->confirmed()
                            ->autocomplete('new-password')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Hash::make($state) : null),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Подтвердите пароль')
                            ->password()
                            ->autocomplete('new-password')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('balance')
                            ->label('Баланс')
                            ->numeric()
                            ->default(0),

                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label('Время активации')
                            ->default(now()),
                    ]),
                ]),

            Section::make('Дополнительно')
                ->schema([
                    Select::make('roles')
                        ->label('Роли')
                        ->multiple()
                        ->preload()
                        ->relationship('roles', 'name')
                        ->searchable()
                        ->live()
                        ->helperText('При выборе роли doctors ниже появится анкета врача.')
                        ->columnSpanFull(),

                    FileUpload::make('avatar')
                        ->label('Аватар пользователя')
                        ->image()
                        ->directory('avatars')
                        ->disk('public')
                        ->visibility('public')
                        ->maxSize(2048),
                ]),

            Section::make('Данные врача')
                ->description('Запись в таблице doctors будет привязана к создаваемому пользователю через doctors.user_id.')
                ->visible(fn (\Filament\Forms\Get $get): bool => self::hasSelectedDoctorRole($get('roles')))
                ->schema([
                    Forms\Components\TextInput::make('doctor_surname')
                        ->label('Фамилия врача')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('doctor_name')
                        ->label('Имя врача')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('doctor_patronym')
                        ->label('Отчество')
                        ->maxLength(255),

                    Forms\Components\DatePicker::make('doctor_birth_at')
                        ->label('Дата рождения')
                        ->required()
                        ->native(false),

                    Forms\Components\TextInput::make('doctor_profession')
                        ->label('Должность')
                        ->placeholder('Например: врач-психиатр')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('doctor_status')
                        ->label('Специализация / статус')
                        ->default('Психиатр')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Textarea::make('doctor_address_job')
                        ->label('Адрес работы / место приёма')
                        ->required()
                        ->rows(2)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('doctor_city')
                        ->label('Город')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('doctor_phone')
                        ->label('Телефон врача')
                        ->tel()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('doctor_email')
                        ->label('Email врача')
                        ->email()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('doctor_experience_years')
                        ->label('Стаж: лет')
                        ->integer()
                        ->minValue(0)
                        ->maxValue(90),

                    Forms\Components\TextInput::make('doctor_experience_months')
                        ->label('Стаж: месяцев')
                        ->integer()
                        ->minValue(0)
                        ->maxValue(11),

                    Forms\Components\Textarea::make('doctor_about')
                        ->label('О враче')
                        ->rows(3)
                        ->columnSpanFull(),

                    FileUpload::make('doctor_photo')
                        ->label('Фото врача')
                        ->disk('public')
                        ->directory('doctors')
                        ->visibility('public')
                        ->image()
                        ->maxSize(4096)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Клиника врача')
                ->description('Можно прикрепить существующую клинику, создать новую или оставить врача частнопрактикующим.')
                ->visible(fn (\Filament\Forms\Get $get): bool => self::hasSelectedDoctorRole($get('roles')))
                ->schema([
                    Select::make('doctor_clinic_mode')
                        ->label('Тип прикрепления')
                        ->options([
                            'private' => 'Частная практика (без клиники)',
                            'existing' => 'Прикрепить к существующей клинике',
                            'new' => 'Создать новую клинику',
                        ])
                        ->default('private')
                        ->required()
                        ->live()
                        ->columnSpanFull(),

                    Select::make('doctor_clinic_id')
                        ->label('Клиника')
                        ->options(fn (): array => \App\Models\Clinic::query()
                            ->whereNull('deleted_at')
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->searchable()
                        ->preload()
                        ->required(fn (\Filament\Forms\Get $get): bool => $get('doctor_clinic_mode') === 'existing')
                        ->visible(fn (\Filament\Forms\Get $get): bool => $get('doctor_clinic_mode') === 'existing')
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('new_clinic_name')
                        ->label('Название новой клиники')
                        ->required(fn (\Filament\Forms\Get $get): bool => $get('doctor_clinic_mode') === 'new')
                        ->maxLength(255)
                        ->visible(fn (\Filament\Forms\Get $get): bool => $get('doctor_clinic_mode') === 'new'),

                    Forms\Components\TextInput::make('new_clinic_city')
                        ->label('Город клиники')
                        ->maxLength(255)
                        ->visible(fn (\Filament\Forms\Get $get): bool => $get('doctor_clinic_mode') === 'new'),

                    Forms\Components\Textarea::make('new_clinic_address')
                        ->label('Адрес клиники')
                        ->required(fn (\Filament\Forms\Get $get): bool => $get('doctor_clinic_mode') === 'new')
                        ->rows(2)
                        ->visible(fn (\Filament\Forms\Get $get): bool => $get('doctor_clinic_mode') === 'new')
                        ->columnSpanFull(),

                    Select::make('new_clinic_type')
                        ->label('Тип клиники')
                        ->options([
                            'outpatient' => 'Амбулатория / поликлиника',
                            'inpatient' => 'Стационар',
                        ])
                        ->default('outpatient')
                        ->live()
                        ->required(fn (\Filament\Forms\Get $get): bool => $get('doctor_clinic_mode') === 'new')
                        ->visible(fn (\Filament\Forms\Get $get): bool => $get('doctor_clinic_mode') === 'new'),

                    Select::make('new_clinic_parent_id')
                        ->label('Головная клиника')
                        ->options(fn (): array => \App\Models\Clinic::query()
                            ->whereNull('deleted_at')
                            ->where('clinic_type', 'outpatient')
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->searchable()
                        ->preload()
                        ->visible(fn (\Filament\Forms\Get $get): bool => $get('doctor_clinic_mode') === 'new'
                            && $get('new_clinic_type') === 'inpatient'),

                    Forms\Components\Toggle::make('new_clinic_set_headmaster')
                        ->label('Назначить этого врача главным врачом новой клиники')
                        ->default(false)
                        ->visible(fn (\Filament\Forms\Get $get): bool => $get('doctor_clinic_mode') === 'new')
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('new_clinic_description')
                        ->label('Описание клиники')
                        ->rows(3)
                        ->columnSpanFull()
                        ->visible(fn (\Filament\Forms\Get $get): bool => $get('doctor_clinic_mode') === 'new'),
                ])
                ->columns(2),
        ]);
    }

    /**
     * Keeps doctor/clinic fields out of mass assignment into the users table.
     * The caller must pass the returned profile data to saveDoctorProfile() after saving the User.
     */
    public static function pullDoctorFields(array &$data): array
    {
        $keys = [
            'doctor_surname', 'doctor_name', 'doctor_patronym', 'doctor_birth_at',
            'doctor_profession', 'doctor_status', 'doctor_address_job', 'doctor_city',
            'doctor_phone', 'doctor_email', 'doctor_experience_years',
            'doctor_experience_months', 'doctor_about', 'doctor_photo',
            'doctor_clinic_mode', 'doctor_clinic_id',
            'new_clinic_name', 'new_clinic_city', 'new_clinic_address',
            'new_clinic_type', 'new_clinic_parent_id', 'new_clinic_description',
            'new_clinic_set_headmaster',
        ];

        $profile = [];

        foreach ($keys as $key) {
            $profile[$key] = $data[$key] ?? null;
            unset($data[$key]);
        }

        return $profile;
    }

    /**
     * Autofill fields when an existing user is edited.
     */
    public static function doctorFormValues(User $user): array
    {
        $doctor = \App\Models\Doctor::query()->where('user_id', $user->id)->first();

        if (! $doctor) {
            return [
                'doctor_clinic_mode' => 'private',
                'doctor_status' => 'Психиатр',
            ];
        }

        return [
            'doctor_surname' => $doctor->surname,
            'doctor_name' => $doctor->name,
            'doctor_patronym' => $doctor->patronym,
            'doctor_birth_at' => $doctor->birth_at
                ? \Illuminate\Support\Carbon::parse($doctor->birth_at)->format('Y-m-d')
                : null,
            'doctor_profession' => $doctor->profession,
            'doctor_status' => $doctor->status,
            'doctor_address_job' => $doctor->address_job,
            'doctor_city' => $doctor->city,
            'doctor_phone' => $doctor->phone,
            'doctor_email' => $doctor->email,
            'doctor_experience_years' => $doctor->experience_years,
            'doctor_experience_months' => $doctor->experience_months,
            'doctor_about' => $doctor->about,
            'doctor_photo' => $doctor->photo,
            'doctor_clinic_mode' => $doctor->clinic_id ? 'existing' : 'private',
            'doctor_clinic_id' => $doctor->clinic_id,
        ];
    }

    /**
     * Called only after the users record and Spatie roles relationship were saved.
     * doctors.user_id is explicitly the target user's ID (not auth()->id()).
     */
    public static function saveDoctorProfile(User $user, array $profile): void
    {
        // Reload roles, as Filament writes the roles relationship after users save.
        $user->unsetRelation('roles');
        if (! $user->hasRole('doctors')) {
            // Do not delete/alter historical doctor records when the role is removed.
            return;
        }

        $mode = $profile['doctor_clinic_mode'] ?? 'private';
        $clinicId = null;

        if ($mode === 'existing') {
            $clinicId = \App\Models\Clinic::query()
                ->whereNull('deleted_at')
                ->findOrFail($profile['doctor_clinic_id'])
                ->getKey();
        } elseif ($mode === 'new') {
            $clinic = new \App\Models\Clinic();
            $clinic->forceFill([
                'name' => $profile['new_clinic_name'],
                'address' => $profile['new_clinic_address'],
                'description' => $profile['new_clinic_description'] ?: null,
                'city' => $profile['new_clinic_city'] ?: null,
                'clinic_type' => $profile['new_clinic_type'] ?: 'outpatient',
                'parent_clinic_id' => ($profile['new_clinic_type'] ?? 'outpatient') === 'inpatient'
                    ? ($profile['new_clinic_parent_id'] ?: null)
                    : null,
            ]);
            $clinic->save();
            $clinicId = $clinic->getKey();
        } elseif ($mode !== 'private') {
            throw new \InvalidArgumentException('Недопустимый вариант прикрепления к клинике.');
        }

        $doctor = \App\Models\Doctor::query()->firstOrNew([
            'user_id' => $user->id,
        ]);

        $doctor->forceFill([
            'user_id' => $user->id,
            'name' => $profile['doctor_name'],
            'surname' => $profile['doctor_surname'],
            'patronym' => $profile['doctor_patronym'] ?: null,
            'birth_at' => $profile['doctor_birth_at'],
            'address_job' => $profile['doctor_address_job'],
            'clinic_id' => $clinicId,
            'status' => $profile['doctor_status'] ?: 'Психиатр',
            'photo' => $profile['doctor_photo'] ?: null,
            'about' => $profile['doctor_about'] ?: null,
            'experience_years' => filled($profile['doctor_experience_years'])
                ? (int) $profile['doctor_experience_years'] : null,
            'experience_months' => filled($profile['doctor_experience_months'])
                ? (int) $profile['doctor_experience_months'] : null,
            'city' => $profile['doctor_city'] ?: null,
            'profession' => $profile['doctor_profession'],
            'phone' => $profile['doctor_phone'] ?: null,
            'email' => $profile['doctor_email'] ?: $user->email,
        ]);
        $doctor->save();

        if ($mode === 'new' && ! empty($profile['new_clinic_set_headmaster'])) {
            $clinic->headmaster_id = $doctor->getKey();
            $clinic->save();
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('login')->label('Логин')->searchable(),
                TextColumn::make('email')->label('Email')->searchable(),
                TextColumn::make('balance')->label('Баланс')->money('RUB'),
                TextColumn::make('created_at')->label('Создано')->dateTime(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Action::make('balanceHistory')
                    ->label('История транзакций')
                    ->action(fn(User $record) => static::showBalanceHistory($record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function showBalanceHistory(User $user): void
    {
        redirect()->to(
            BalanceTransactionResource::getUrl('index', [
                'tableFilters[user_id][value]' => $user->id,
            ])
        );
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

}
