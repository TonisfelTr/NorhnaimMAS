<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiagnoseResource\Pages;
use App\Models\Diagnose;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DiagnoseResource extends Resource
{
    protected static ?string $model = Diagnose::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Диагнозы';
    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $modelLabel = 'диагноз';
    protected static ?string $pluralModelLabel = 'диагнозы';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Основные данные')->schema([
                Forms\Components\TextInput::make('code')->label('Код')->required()->maxLength(255),
                Forms\Components\TextInput::make('title')->label('Название')->required()->maxLength(255),
                Forms\Components\RichEditor::make('description')->label('Описание'),
            ])->columns(2),

            Forms\Components\Section::make('Критерии')->schema([
                Forms\Components\TextInput::make('required_criteria')->label('Обязательные')->numeric()->default(1),
                Forms\Components\TextInput::make('relative_criteria')->label('Относительные')->numeric()->default(1),
            ])->columns(2),

            Forms\Components\Section::make('Связанные диагнозы')->schema([
                Forms\Components\Repeater::make('relatives')
                    ->label('Родственные диагнозы')
                    ->schema([
                        Forms\Components\Select::make('code')
                            ->options(Diagnose::pluck('title', 'code'))
                            ->label('Диагноз')
                            ->required(),
                    ])
                    ->formatStateUsing(fn ($state) => collect($state)->map(fn ($code) => ['code' => $code])->toArray())
                    ->dehydrateStateUsing(fn ($state) => collect($state)->pluck('code')->toArray())
                    ->defaultItems(0),

                Forms\Components\Repeater::make('exceptions')
                    ->label('Исключенные диагнозы')
                    ->schema([
                        Forms\Components\Select::make('code')
                            ->options(Diagnose::pluck('title', 'code'))
                            ->label('Диагноз')
                            ->required(),
                    ])
                    ->formatStateUsing(fn ($state) => collect($state)->map(fn ($code) => ['code' => $code])->toArray())
                    ->dehydrateStateUsing(fn ($state) => collect($state)->pluck('code')->toArray())
                    ->defaultItems(0),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('code')->label('Код')->searchable(),
                Tables\Columns\TextColumn::make('title')->label('Название')->searchable(),
            ])
            ->filters([
                // Здесь можно добавить фильтры
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->authorize(fn ($record) => auth()->user()->can('diagnose_remove')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->authorize(fn () => auth()->user()->can('diagnose_remove')),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDiagnoses::route('/'),
            'create' => Pages\CreateDiagnose::route('/create'),
            'edit' => Pages\EditDiagnose::route('/{record}/edit'),
        ];
    }
}
