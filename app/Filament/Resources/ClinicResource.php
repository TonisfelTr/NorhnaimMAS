<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClinicResource\Pages;
use App\Models\Clinic;
use App\Models\Doctor;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class ClinicResource extends Resource
{
    protected static ?string $model = Clinic::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $navigationLabel = 'Клиники';
    protected static ?string $pluralLabel = 'Клиники';
    protected static ?string $modelLabel = 'Клиника';
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        $mainFields = [
            TextInput::make('name')
                ->label('Название')
                ->required()
                ->maxLength(255),

            Select::make('clinic_type')
                ->label('Тип клиники')
                ->options([
                    'outpatient' => 'Амбулатория',
                    'inpatient' => 'Стационар',
                ])
                ->default('outpatient')
                ->required()
                ->live(),

            TextInput::make('city')
                ->label('Город')
                ->maxLength(255),

            TextInput::make('address')
                ->label('Адрес')
                ->required()
                ->maxLength(2000),

            Textarea::make('description')
                ->label('Описание')
                ->rows(4)
                ->columnSpanFull(),
        ];

        if (Schema::hasColumn('clinics', 'status')) {
            $mainFields[] = Select::make('status')
                ->label('Статус')
                ->options([
                    'active' => 'Активна',
                    'inactive' => 'Неактивна',
                ])
                ->nullable();
        }

        if (Schema::hasColumn('clinics', 'parent_clinic_id')) {
            $mainFields[] = Select::make('parent_clinic_id')
                ->label('Головная организация')
                ->options(
                    fn (): array => Clinic::query()
                        ->where('clinic_type', 'outpatient')
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all()
                )
                ->searchable()
                ->preload()
                ->nullable()
                ->visible(
                    fn (Get $get): bool =>
                        $get('clinic_type') === 'inpatient'
                )
                ->helperText(
                    'Для стационара можно указать головную амбулаторную организацию.'
                );
        }

        if (Schema::hasColumn('clinics', 'headmaster_id')) {
            $mainFields[] = Select::make('headmaster_id')
                ->label('Главный врач')
                ->options(
                    fn (): array => Doctor::query()
                        ->orderBy('surname')
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(
                            static fn (Doctor $doctor): array => [
                                $doctor->id => trim(
                                    $doctor->surname . ' '
                                    . $doctor->name . ' '
                                    . ($doctor->patronym ?? '')
                                ),
                            ]
                        )
                        ->all()
                )
                ->searchable()
                ->preload()
                ->nullable();
        }

        if (Schema::hasColumn('clinics', 'okato')) {
            $mainFields[] = TextInput::make('okato')
                ->label('ОКАТО')
                ->maxLength(32)
                ->helperText('Официальный код медицинской организации.');
        }

        if (Schema::hasColumn('clinics', 'latitude')) {
            $mainFields[] = TextInput::make('latitude')
                ->label('Широта')
                ->numeric()
                ->disabled()
                ->dehydrated(false);
        }

        if (Schema::hasColumn('clinics', 'longitude')) {
            $mainFields[] = TextInput::make('longitude')
                ->label('Долгота')
                ->numeric()
                ->disabled()
                ->dehydrated(false);
        }

        $inpatientFields = [];

        if (Schema::hasColumn('clinics', 'inpatient_mode')) {
            $inpatientFields[] = Select::make('inpatient_mode')
                ->label('Режим стационара')
                ->options([
                    'round_the_clock' => 'Круглосуточный',
                    'day' => 'Дневной',
                    'night' => 'Ночной',
                ])
                ->default('round_the_clock')
                ->required(
                    fn (Get $get): bool =>
                        $get('clinic_type') === 'inpatient'
                );
        }

        $inpatientFields[] = Placeholder::make('inpatient_schedule_notice')
            ->label('Рабочее время и посещения')
            ->content(
                'Подробное расписание работы стационара и время посещения '
                . 'родственниками настраиваются в разделе «Настройки клиники».'
            );

        return $form
            ->schema([
                Section::make('Основные данные')
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ])
                    ->schema($mainFields),

                Section::make('Контакты')
                    ->schema([
                        Repeater::make('phones')
                            ->label('Телефоны')
                            ->schema([
                                TextInput::make('label')
                                    ->label('Подпись')
                                    ->maxLength(255)
                                    ->placeholder('Основная регистратура'),

                                TextInput::make('phone')
                                    ->label('Телефон')
                                    ->tel()
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel('Добавить телефон')
                            ->reorderable(),
                    ]),

                Section::make('Форматы амбулаторного приёма')
                    ->visible(
                        fn (Get $get): bool =>
                            $get('clinic_type') !== 'inpatient'
                    )
                    ->schema([
                        CheckboxList::make('services')
                            ->label('Разрешённые форматы')
                            ->options([
                                'in_person' => 'Очный приём',
                                'telemedicine' => 'Телемедицина',
                            ])
                            ->columns(2)
                            ->default([
                                'in_person',
                            ])
                            ->helperText(
                                'Готовность конкретного врача к телемедицине '
                                . 'проверяется отдельно в профиле врача.'
                            ),
                    ]),

                Section::make('Параметры стационара')
                    ->visible(
                        fn (Get $get): bool =>
                            $get('clinic_type') === 'inpatient'
                    )
                    ->columns(2)
                    ->schema($inpatientFields),

                Section::make('Фотографии клиники')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('photos')
                            ->label('Фотографии')
                            ->collection('photos')
                            ->multiple()
                            ->image()
                            ->imageEditor()
                            ->reorderable()
                            ->maxFiles(12)
                            ->maxSize(10240)
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                            ])
                            ->helperText(
                                'До 12 фотографий. JPG, PNG или WEBP, до 10 МБ каждая.'
                            ),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('photos')
                    ->label('Фото')
                    ->collection('photos')
                    ->square()
                    ->stacked()
                    ->limit(3)
                    ->limitedRemainingText(),

                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),

                TextColumn::make('clinic_type')
                    ->label('Тип')
                    ->badge()
                    ->formatStateUsing(
                        static fn (?string $state): string =>
                            $state === 'inpatient'
                                ? 'Стационар'
                                : 'Амбулатория'
                    )
                    ->color(
                        static fn (?string $state): string =>
                            $state === 'inpatient'
                                ? 'warning'
                                : 'info'
                    )
                    ->sortable(),

                TextColumn::make('city')
                    ->label('Город')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('address')
                    ->label('Адрес')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('phones')
                    ->label('Телефоны')
                    ->formatStateUsing(
                        static function (mixed $state): string {
                            if (is_string($state)) {
                                $decoded = json_decode($state, true);
                                $state = is_array($decoded) ? $decoded : [];
                            }

                            if (!is_array($state)) {
                                return '—';
                            }

                            $phones = collect($state)
                                ->pluck('phone')
                                ->filter(
                                    static fn ($phone): bool =>
                                        trim((string) $phone) !== ''
                                )
                                ->map(
                                    static fn ($phone): string =>
                                        trim((string) $phone)
                                )
                                ->values();

                            return $phones->isNotEmpty()
                                ? $phones->implode(', ')
                                : '—';
                        }
                    )
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('services')
                    ->label('Форматы')
                    ->formatStateUsing(
                        static function (mixed $state): string {
                            if (is_string($state)) {
                                $decoded = json_decode($state, true);
                                $state = is_array($decoded) ? $decoded : [];
                            }

                            $items = is_array($state)
                                ? $state
                                : [];

                            $labels = [];

                            if (in_array('in_person', $items, true)) {
                                $labels[] = 'Очно';
                            }

                            if (in_array('telemedicine', $items, true)) {
                                $labels[] = 'Телемедицина';
                            }

                            return $labels !== []
                                ? implode(', ', $labels)
                                : '—';
                        }
                    )
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Добавлена')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('clinic_type')
                    ->label('Тип клиники')
                    ->options([
                        'outpatient' => 'Амбулатория',
                        'inpatient' => 'Стационар',
                    ]),

                SelectFilter::make('city')
                    ->label('Город')
                    ->options(
                        fn (): array => Clinic::query()
                            ->whereNotNull('city')
                            ->where('city', '<>', '')
                            ->select('city')
                            ->distinct()
                            ->orderBy('city')
                            ->pluck('city', 'city')
                            ->all()
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Редактировать'),

                Tables\Actions\DeleteAction::make()
                    ->label('Удалить')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Удалить выбранные')
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('name')
            ->striped()
            ->persistFiltersInSession()
            ->persistSearchInSession();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('media');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClinics::route('/'),
            'create' => Pages\CreateClinic::route('/create'),
            'edit' => Pages\EditClinic::route('/{record}/edit'),
        ];
    }
}
