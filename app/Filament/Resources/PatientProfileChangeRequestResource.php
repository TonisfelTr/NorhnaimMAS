<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatientProfileChangeRequestResource\Pages;
use App\Models\PatientProfileChangeRequest;
use App\Services\PatientProfileChangeService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PatientProfileChangeRequestResource extends Resource
{
    protected static ?string $model = PatientProfileChangeRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationLabel = 'Запросы на изменение данных';
    protected static ?string $navigationGroup = 'Пациенты';
    protected static ?string $modelLabel = 'запрос на изменение данных';
    protected static ?string $pluralModelLabel = 'запросы на изменение данных';
    protected static ?int $navigationSort = 6;

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

                Tables\Columns\TextColumn::make('patient.surname')
                    ->label('Пациент')
                    ->getStateUsing(fn (PatientProfileChangeRequest $record): string =>
                        trim(implode(' ', array_filter([
                            $record->patient?->surname,
                            $record->patient?->name,
                            $record->patient?->patronym,
                        ])))
                    )
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.login')
                    ->label('Аккаунт')
                    ->searchable(),

                Tables\Columns\TextColumn::make('reviewClinic.name')
                    ->label('Клиника')
                    ->placeholder('Не указана')
                    ->wrap(),

                Tables\Columns\TextColumn::make('review_target')
                    ->label('Кому направлен')
                    ->formatStateUsing(fn (string $state): string =>
                        PatientProfileChangeRequest::targetOptions()[$state] ?? $state
                    ),

                Tables\Columns\TextColumn::make('changed_fields')
                    ->label('Изменяемые поля')
                    ->getStateUsing(fn (PatientProfileChangeRequest $record): string =>
                        implode(', ', array_map(
                            fn (string $field): string => self::fieldLabel($field),
                            $record->changed_fields ?? []
                        ))
                    )
                    ->wrap(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        PatientProfileChangeRequest::STATUS_PENDING => 'Ожидает рассмотрения',
                        PatientProfileChangeRequest::STATUS_APPROVED => 'Одобрен',
                        PatientProfileChangeRequest::STATUS_REJECTED => 'Отклонён',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        PatientProfileChangeRequest::STATUS_APPROVED => 'success',
                        PatientProfileChangeRequest::STATUS_REJECTED => 'danger',
                        default => 'warning',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата заявки')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('details')
                    ->label('Открыть')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Запрос пациента')
                    ->modalWidth('3xl')
                    ->modalContent(fn (PatientProfileChangeRequest $record) =>
                        view('filament.patient-profile-change-requests.details', [
                            'request' => $record,
                        ])
                    )
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Закрыть'),

                Tables\Actions\Action::make('approve')
                    ->label('Одобрить')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (PatientProfileChangeRequest $record): bool => $record->isPending())
                    ->form([
                        Forms\Components\Textarea::make('review_note')
                            ->label('Комментарий пациенту')
                            ->maxLength(2000),
                    ])
                    ->action(function (PatientProfileChangeRequest $record, array $data): void {
                        app(PatientProfileChangeService::class)->approve(
                            $record,
                            auth()->user(),
                            $data['review_note'] ?? null
                        );

                        Notification::make()
                            ->title('Изменения подтверждены')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Отклонить')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (PatientProfileChangeRequest $record): bool => $record->isPending())
                    ->form([
                        Forms\Components\Textarea::make('review_note')
                            ->label('Причина отказа')
                            ->required()
                            ->minLength(5)
                            ->maxLength(2000),
                    ])
                    ->action(function (PatientProfileChangeRequest $record, array $data): void {
                        app(PatientProfileChangeService::class)->reject(
                            $record,
                            auth()->user(),
                            (string) $data['review_note']
                        );

                        Notification::make()
                            ->title('Запрос отклонён')
                            ->warning()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['patient', 'user', 'reviewer', 'reviewClinic', 'reviewDoctor']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPatientProfileChangeRequests::route('/'),
        ];
    }

    private static function fieldLabel(string $field): string
    {
        return [
            'surname' => 'Фамилия',
            'name' => 'Имя',
            'patronym' => 'Отчество',
            'birth_at' => 'Дата рождения',
            'gender' => 'Пол',
            'phone' => 'Телефон',
            'email' => 'Email',
            'birth_place' => 'Место рождения',
            'address_registration' => 'Адрес регистрации',
            'address_residence' => 'Адрес проживания',
            'snils' => 'СНИЛС',
            'oms' => 'ОМС',
            'insurance_company' => 'Страховая компания',
            'document_type' => 'Тип документа',
            'document_series' => 'Серия',
            'document_number' => 'Номер',
            'document_issued_by' => 'Кем выдан',
            'document_issued_at' => 'Дата выдачи',
            'document_department_code' => 'Код подразделения',
        ][$field] ?? $field;
    }
}
