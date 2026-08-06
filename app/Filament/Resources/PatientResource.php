<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatientResource\Pages;
use App\Models\Patient;
use App\Models\Diagnose;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Http;

class PatientResource extends Resource
{
    protected static ?string $model = Patient::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Пациенты';
    protected static ?string $navigationGroup = 'Пользователи';
    protected static ?string $modelLabel = 'пациента';
    protected static ?string $pluralModelLabel = 'пациенты';

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Личные данные')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('surname')
                        ->required()->maxLength(255)->label('Фамилия'),
                    Forms\Components\TextInput::make('name')
                        ->required()->maxLength(255)->label('Имя'),
                    Forms\Components\TextInput::make('patronym')
                        ->maxLength(255)->label('Отчество'),
                    Forms\Components\DatePicker::make('birth_at')
                        ->required()->label('Дата рождения')
                        ->columnSpan(3)
                ]),

            Forms\Components\Section::make('Адреса')
                ->columns(1)
                ->schema([
                    self::addressAutocomplete('address_registration', 'Адрес регистрации'),
                    self::addressAutocomplete('address_residence', 'Адрес проживания'),
                    self::addressAutocomplete('address_job', 'Адрес работы'),
                ]),

            Forms\Components\Section::make('Дополнительно')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('diagnose_id')
                        ->label('Диагноз')
                        ->relationship(
                            name: 'diagnose',
                            titleAttribute: 'title',
                            modifyQueryUsing: fn($query) => $query->orderBy('title')
                        )
                        ->getSearchResultsUsing(fn (string $search) => Diagnose::where('title', 'ilike', "%{$search}%")
                            ->orWhere('code', 'ilike', "%{$search}%")
                            ->limit(50)
                            ->pluck('title', 'id'))
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->code} - {$record->title}")
                        ->searchable(),

                    Forms\Components\TextInput::make('profession')->label('Профессия'),
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'login')
                        ->searchable()
                        ->label('Пользователь')
                        ->columnSpan(2),
                    Grid::make()
                        ->schema([
                            Forms\Components\Toggle::make('socially_dangerous')->label('Социально опасный')->columns(1),
                            Forms\Components\Toggle::make('disability')->label('Инвалидность')->columns(1),
                            Forms\Components\Toggle::make('married')->label('В браке')->columns(1),
                        ])
                        ->columns(3),
                ]),
        ]);
    }

    protected static function addressAutocomplete(string $name, string $label): Forms\Components\Select
    {
        return Forms\Components\Select::make($name)
            ->label($label)
            ->searchable()
            ->options(function ($state) {
                return $state ? [$state => $state] : [];
            })
            ->getSearchResultsUsing(function (string $search) {
                $token = config('dadata.dadata_api');
                if (strlen($search) < 3 || !$token) {
                    return [];
                }

                $response = Http::withHeaders([
                    'Authorization' => "Token $token",
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])->post('https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address', [
                    'query' => $search,
                    'count' => 5,
                ]);

                $suggestions = $response->json('suggestions');

                // фильтрация пустых значений
                return collect($suggestions)
                    ->pluck('value', 'value')
                    ->filter()
                    ->toArray();
            })
            ->getOptionLabelUsing(fn($value) => (string)$value)
            ->noSearchResultsMessage('Адрес не найден. Продолжайте вводить...')
            ->allowHtml();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('surname')->sortable()->searchable()->label('Фамилия'),
                Tables\Columns\TextColumn::make('name')->sortable()->searchable()->label('Имя'),
                Tables\Columns\TextColumn::make('patronym')->searchable()->label('Отчество'),
                Tables\Columns\TextColumn::make('birth_at')->date('d.m.Y')->sortable()->label('Дата рождения'),
                Tables\Columns\TextColumn::make('diagnose.title')->label('Диагноз'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('socially_dangerous')->label('Социально опасный'),
                Tables\Filters\TernaryFilter::make('disability')->label('Инвалидность'),
                Tables\Filters\TernaryFilter::make('married')->label('В браке'),
                Tables\Filters\SelectFilter::make('diagnose_id')
                    ->label('Диагноз')
                    ->relationship('diagnose', 'title'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPatients::route('/'),
            'create' => Pages\CreatePatient::route('/create'),
            'edit' => Pages\EditPatient::route('/{record}/edit'),
        ];
    }
}
