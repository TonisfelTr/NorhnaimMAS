<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegistryRecordResource\Pages;
use App\Models\RegistryRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RegistryRecordResource extends Resource
{
    protected static ?string $model = RegistryRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Регистратура';
    protected static ?string $navigationGroup = 'Регистратура';
    protected static ?string $modelLabel = 'запись в регистратуре';
    protected static ?string $pluralModelLabel = 'записи в регистратуре';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Данные записи')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('doctor_id')
                        ->label('Врач')
                        ->relationship('doctor', 'surname')
                        ->searchable()
                        ->getOptionLabelFromRecordUsing(fn($record) => "{$record->surname} {$record->name} {$record->patronym}")
                        ->required(),

                    Forms\Components\Select::make('patient_id')
                        ->label('Пациент')
                        ->relationship('patient', 'surname')
                        ->searchable()
                        ->getOptionLabelFromRecordUsing(fn($record) => "{$record->surname} {$record->name} {$record->patronym}")
                        ->required(),

                    Forms\Components\DateTimePicker::make('for_datetime')
                        ->label('Дата и время записи')
                        ->required(),

                    Forms\Components\Toggle::make('appointment')
                        ->label('Приём состоялся')
                        ->default(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('doctor.surname')
                    ->label('Врач')
                    ->formatStateUsing(fn($record) => "{$record->doctor->surname} {$record->doctor->name}")
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('patient.surname')
                    ->label('Пациент')
                    ->formatStateUsing(fn($record) => "{$record->patient->surname} {$record->patient->name}")
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('for_datetime')
                    ->label('Дата и время')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                Tables\Columns\IconColumn::make('appointment')
                    ->label('Приём состоялся')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('appointment')
                    ->label('Приём состоялся'),
                Tables\Filters\SelectFilter::make('doctor_id')
                    ->label('Врач')
                    ->relationship('doctor', 'surname'),
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
            'index' => Pages\ListRegistryRecords::route('/'),
            'create' => Pages\CreateRegistryRecord::route('/create'),
            'edit' => Pages\EditRegistryRecord::route('/{record}/edit'),
        ];
    }
}
