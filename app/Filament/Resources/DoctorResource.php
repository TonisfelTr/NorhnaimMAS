<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DoctorResource\Pages;
use App\Models\Doctor;
use App\Models\Clinic;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DoctorResource extends Resource
{
    protected static ?string $model = Doctor::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Пользователи';
    protected static ?string $navigationLabel = 'Врачи';
    protected static ?string $pluralLabel = 'Врачи';
    protected static ?string $modelLabel = 'Врач';

    protected static ?string $recordTitleAttribute = 'surname';

    public static function form(Form $form): Form
    {
        $photoField = class_exists(SpatieMediaLibraryFileUpload::class)
            ? SpatieMediaLibraryFileUpload::make('photo')
                ->collection('photos')
                ->image()
                ->imageEditor()
                ->imageCropAspectRatio('4:5')
                ->maxSize(8192)
                ->label('Фотография')
                ->helperText('JPG, PNG или WEBP. Рекомендуем портретное фото 4:5.')
            : Placeholder::make('photo_plugin_missing')
                ->label('Фотография')
                ->content(
                    'Плагин filament/spatie-laravel-media-library-plugin не установлен. '
                    . 'Сам список врачей при этом продолжит работать.'
                );

        return $form
            ->schema([
                Section::make('Основная информация')
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ])
                    ->schema([
                        TextInput::make('surname')
                            ->label('Фамилия')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('name')
                            ->label('Имя')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('patronym')
                            ->label('Отчество')
                            ->maxLength(255),

                        DatePicker::make('birth_at')
                            ->label('Дата рождения')
                            ->required()
                            ->native(false)
                            ->maxDate(now()),

                        TextInput::make('snils')
                            ->label('СНИЛС')
                            ->maxLength(32)
                            ->placeholder('000-000-000 00')
                            ->helperText('Используется для будущей синхронизации с ФРМР/ЕГИСЗ.'),

                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->maxLength(255),
                    ]),

                Section::make('Работа и профессиональный профиль')
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ])
                    ->schema([
                        Select::make('clinic_id')
                            ->relationship(
                                name: 'clinic',
                                titleAttribute: 'name',
                            )
                            ->label('Клиника')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set): void {
                                $clinic = filled($state)
                                    ? Clinic::query()
                                        ->select(['id', 'city', 'address'])
                                        ->find($state)
                                    : null;

                                // При выборе клиники эти поля всегда берутся из её настроек.
                                // При снятии привязки очищаем их, чтобы врач мог ввести свои данные.
                                $set(
                                    'city',
                                    $clinic?->city !== null
                                        ? trim((string) $clinic->city)
                                        : null
                                );

                                $set(
                                    'address_job',
                                    $clinic?->address !== null
                                        ? trim((string) $clinic->address)
                                        : ''
                                );
                            })
                            ->nullable(),

                        TextInput::make('profession')
                            ->label('Должность')
                            ->required()
                            ->maxLength(255)
                            ->default('Врач'),

                        TextInput::make('status')
                            ->label('Специальность')
                            ->required()
                            ->maxLength(255)
                            ->default('Психиатр'),

                        TextInput::make('city')
                            ->label('Город')
                            ->maxLength(255)
                            ->disabled(fn (Get $get): bool => filled($get('clinic_id')))
                            ->dehydrated()
                            ->helperText(fn (Get $get): string => filled($get('clinic_id'))
                                ? 'Город автоматически берётся из настроек выбранной клиники. Для изменения откройте настройки клиники.'
                                : 'Для врача без привязки к клинике город можно указать вручную.'
                            ),

                        Textarea::make('address_job')
                            ->label('Место работы / рабочий адрес')
                            ->required(fn (Get $get): bool => blank($get('clinic_id')))
                            ->disabled(fn (Get $get): bool => filled($get('clinic_id')))
                            ->dehydrated()
                            ->rows(2)
                            ->columnSpan([
                                'default' => 1,
                                'xl' => 2,
                            ])
                            ->helperText(fn (Get $get): string => filled($get('clinic_id'))
                                ? 'Адрес автоматически берётся из настроек выбранной клиники. Для изменения откройте настройки клиники.'
                                : 'Для врача без привязки к клинике укажите место работы или рабочий адрес.'
                            ),

                        TextInput::make('phone')
                            ->label('Телефон')
                            ->tel()
                            ->maxLength(255)
                            ->helperText(
                                'Это резервный телефон врача. Если назначен кабинет, рабочий телефон берётся из кабинета.'
                            ),

                        Toggle::make('ready_for_telemedicine')
                            ->label('Готов к телемедицине')
                            ->default(false),
                    ]),

                Section::make('Дополнительная информация')
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->schema([
                        TextInput::make('experience_years')
                            ->label('Стаж — лет')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(80),

                        TextInput::make('experience_months')
                            ->label('Стаж — дополнительных месяцев')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(11),

                        Select::make('user_id')
                            ->relationship(
                                name: 'user',
                                titleAttribute: 'login',
                            )
                            ->label('Привязанный пользователь')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        $photoField,

                        Textarea::make('about')
                            ->label('О враче')
                            ->rows(5)
                            ->maxLength(5000)
                            ->columnSpanFull(),
                    ]),

                Section::make('ЕГИСЗ / аккредитация')
                    ->description(
                        'Служебные поля. Их не следует вручную выдавать за подтверждённые данные '
                        . 'до подключения официальной интеграции.'
                    )
                    ->collapsed()
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ])
                    ->schema([
                        TextInput::make('frmr_id')
                            ->label('ФРМР ID')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('accreditation_status')
                            ->label('Статус аккредитации')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('accreditation_number')
                            ->label('Номер аккредитации')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('accreditation_speciality')
                            ->label('Специальность аккредитации')
                            ->disabled()
                            ->dehydrated(false),

                        DatePicker::make('accreditation_valid_from')
                            ->label('Действует с')
                            ->disabled()
                            ->dehydrated(false),

                        DatePicker::make('accreditation_valid_until')
                            ->label('Действует до')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('professional_verification_source')
                            ->label('Источник проверки')
                            ->disabled()
                            ->dehydrated(false),

                        DatePicker::make('professional_verified_at')
                            ->label('Дата последней проверки')
                            ->disabled()
                            ->dehydrated(false),
                    ]),

                Section::make('Прайс-лист услуг врача')
                    ->collapsed()
                    ->schema([
                        Repeater::make('pricelists')
                            ->relationship('pricelists')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Название услуги')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('group')
                                    ->label('Группа услуг')
                                    ->maxLength(255),

                                TextInput::make('price')
                                    ->label('Цена (₽)')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0),

                                TextInput::make('discount_price')
                                    ->label('Цена со скидкой (₽)')
                                    ->numeric()
                                    ->minValue(0),
                            ])
                            ->columns([
                                'default' => 1,
                                'md' => 2,
                                'xl' => 4,
                            ])
                            ->defaultItems(0)
                            ->addActionLabel('Добавить услугу'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo_url')
                    ->label('')
                    ->square()
                    ->height(44)
                    ->width(44),

                TextColumn::make('surname')
                    ->label('Фамилия')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('patronym')
                    ->label('Отчество')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('profession')
                    ->label('Должность')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('status')
                    ->label('Специальность')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('clinic.name')
                    ->label('Клиника')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Частная практика')
                    ->wrap(),

                TextColumn::make('city')
                    ->label('Город')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('ready_for_telemedicine')
                    ->label('Телемедицина')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Добавлен')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('city')
                    ->label('Город')
                    ->options(
                        fn (): array => Doctor::query()
                            ->whereNotNull('city')
                            ->where('city', '<>', '')
                            ->select('city')
                            ->distinct()
                            ->orderBy('city')
                            ->pluck('city', 'city')
                            ->all()
                    ),

                SelectFilter::make('profession')
                    ->label('Должность')
                    ->options(
                        fn (): array => Doctor::query()
                            ->whereNotNull('profession')
                            ->where('profession', '<>', '')
                            ->select('profession')
                            ->distinct()
                            ->orderBy('profession')
                            ->pluck('profession', 'profession')
                            ->all()
                    ),

                SelectFilter::make('status')
                    ->label('Специальность')
                    ->options(
                        fn (): array => Doctor::query()
                            ->whereNotNull('status')
                            ->where('status', '<>', '')
                            ->select('status')
                            ->distinct()
                            ->orderBy('status')
                            ->pluck('status', 'status')
                            ->all()
                    ),

                SelectFilter::make('clinic_id')
                    ->label('Клиника')
                    ->relationship(
                        name: 'clinic',
                        titleAttribute: 'name',
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Редактировать'),

                Tables\Actions\DeleteAction::make()
                    ->label('Удалить'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Удалить выбранные'),
                ]),
            ])
            ->defaultSort('surname')
            ->striped()
            ->persistFiltersInSession()
            ->persistSearchInSession();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'clinic',
                'user',
                'media',
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDoctors::route('/'),
            'create' => Pages\CreateDoctor::route('/create'),
            'edit' => Pages\EditDoctor::route('/{record}/edit'),
        ];
    }
}
