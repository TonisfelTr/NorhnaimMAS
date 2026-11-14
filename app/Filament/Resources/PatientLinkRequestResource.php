<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatientLinkRequestResource\Pages;
use App\Models\Patient;
use App\Models\PatientLinkRequest;
use App\Services\PatientLinkingService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PatientLinkRequestResource extends Resource
{
    protected static ?string $model = PatientLinkRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationLabel = 'Заявки на привязку карт';
    protected static ?string $navigationGroup = 'Пациенты';
    protected static ?string $modelLabel = 'заявку на привязку карты';
    protected static ?string $pluralModelLabel = 'заявки на привязку карт';
    protected static ?int $navigationSort = 5;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('adminpanel_see') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('№')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.login')
                    ->label('Аккаунт')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email аккаунта')
                    ->searchable(),

                Tables\Columns\TextColumn::make('payload.surname')
                    ->label('Фамилия по заявке')
                    ->getStateUsing(fn (PatientLinkRequest $record): string =>
                        trim(implode(' ', array_filter([
                            data_get($record->payload, 'surname'),
                            data_get($record->payload, 'name'),
                            data_get($record->payload, 'patronym'),
                        ])))
                    )
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        PatientLinkRequest::STATUS_PENDING_EMAIL => 'Ожидает email-код',
                        PatientLinkRequest::STATUS_PENDING_REVIEW => 'На ручной проверке',
                        PatientLinkRequest::STATUS_APPROVED => 'Привязана',
                        PatientLinkRequest::STATUS_REJECTED => 'Отклонена',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        PatientLinkRequest::STATUS_APPROVED => 'success',
                        PatientLinkRequest::STATUS_REJECTED => 'danger',
                        PatientLinkRequest::STATUS_PENDING_EMAIL => 'info',
                        default => 'warning',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата заявки')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Проверить и привязать')
                    ->icon('heroicon-o-link')
                    ->color('success')
                    ->visible(fn (PatientLinkRequest $record): bool =>
                        $record->isPending()
                    )
                    ->modalHeading('Ручная проверка привязки')
                    ->modalWidth('3xl')
                    ->modalContent(fn (PatientLinkRequest $record) =>
                        view('filament.patient-link-requests.review-details', [
                            'request' => $record,
                        ])
                    )
                    ->form([
                        Forms\Components\Select::make('patient_id')
                            ->label('Медицинская карта')
                            ->required()
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search): array {
                                return Patient::query()
                                    ->where(function (Builder $query) use ($search): void {
                                        $query
                                            ->where('surname', 'ilike', '%' . $search . '%')
                                            ->orWhere('name', 'ilike', '%' . $search . '%')
                                            ->orWhere('snils', 'ilike', '%' . $search . '%');
                                    })
                                    ->orderBy('surname')
                                    ->limit(40)
                                    ->get()
                                    ->mapWithKeys(fn (Patient $patient): array => [
                                        $patient->id => self::patientOptionLabel($patient),
                                    ])
                                    ->all();
                            })
                            ->getOptionLabelUsing(function ($value): ?string {
                                $patient = Patient::query()->find($value);

                                return $patient
                                    ? self::patientOptionLabel($patient)
                                    : null;
                            }),

                        Forms\Components\Toggle::make('apply_submitted_data')
                            ->label('Сохранить проверенные данные из заявки в медицинскую карту')
                            ->helperText('Если выключено, карта только привязывается. Заполненные сведения не перезаписываются.')
                            ->default(false),
                    ])
                    ->action(function (
                        PatientLinkRequest $record,
                        array $data
                    ): void {
                        app(PatientLinkingService::class)->approveManually(
                            $record,
                            Patient::query()->findOrFail((int) $data['patient_id']),
                            auth()->user(),
                            (bool) ($data['apply_submitted_data'] ?? false)
                        );

                        Notification::make()
                            ->title('Медицинская карта привязана')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Отклонить')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (PatientLinkRequest $record): bool =>
                        $record->isPending()
                    )
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Причина отклонения')
                            ->required()
                            ->maxLength(2000),
                    ])
                    ->action(function (
                        PatientLinkRequest $record,
                        array $data
                    ): void {
                        app(PatientLinkingService::class)->rejectManually(
                            $record,
                            auth()->user(),
                            (string) $data['reason']
                        );

                        Notification::make()
                            ->title('Заявка отклонена')
                            ->warning()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'patient', 'reviewedBy']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPatientLinkRequests::route('/'),
        ];
    }

    private static function patientOptionLabel(Patient $patient): string
    {
        $name = trim(implode(' ', array_filter([
            $patient->surname,
            $patient->name,
            $patient->patronym,
        ])));

        $birthAt = $patient->getRawOriginal('birth_at');

        return sprintf(
            '#%d — %s — %s',
            $patient->id,
            $name,
            $birthAt ? date('d.m.Y', strtotime($birthAt)) : 'дата не указана'
        );
    }
}
