<?php

namespace App\Filament\Resources;

use App\Enums\MedicineTypesEnum;
use App\Filament\Resources\DrugResource\Pages;
use App\Models\Drug;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;
use Illuminate\Support\Collection;

class DrugResource extends Resource
{
    protected static ?string $model = Drug::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationLabel = 'Лекарства';
    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $label = 'лекарство';
    protected static ?string $pluralModelLabel = 'лекарства';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Название')
                    ->required()
                    ->maxLength(50)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('latin_name')
                    ->label('Латинское название')
                    ->maxLength(255),

                Forms\Components\Select::make('group')
                    ->label('Группа')
                    ->options(collect(MedicineTypesEnum::cases())->mapWithKeys(fn($type) => [
                        $type->value => MedicineTypesEnum::getMatched((int)$type->value)
                    ])->toArray()),

                Forms\Components\Fieldset::make('Диапазон выхода')
                    ->schema([
                        Forms\Components\TextInput::make('ht_output_from')
                            ->label('От')
                            ->numeric(),

                        Forms\Components\TextInput::make('ht_output_to')
                            ->label('До')
                            ->numeric(),
                    ])->columns(2),

                Forms\Components\Tabs::make('Формы выпуска')->schema([
                    Forms\Components\Tabs\Tab::make('Таблетки')->schema([
                        Forms\Components\Repeater::make('forms.tablets')
                            ->label('Таблетки (дозировка/количество шт.)')
                            ->schema([
                                Forms\Components\TextInput::make('dose')
                                    ->label('Дозировка (мг)')
                                    ->numeric()
                                    ->required(),

                                Forms\Components\TextInput::make('quantity')
                                    ->label('Количество в упаковке (шт)')
                                    ->numeric()
                                    ->required(),
                            ])
                            ->defaultItems(0),
                    ]),

                    Forms\Components\Tabs\Tab::make('Драже')->schema([
                        Forms\Components\Repeater::make('forms.dragees')
                            ->label('Драже (дозировка/количество шт.)')
                            ->schema([
                                Forms\Components\TextInput::make('dose')
                                    ->label('Дозировка (мг)')
                                    ->numeric()
                                    ->required(),

                                Forms\Components\TextInput::make('quantity')
                                    ->label('Количество в упаковке (шт)')
                                    ->numeric()
                                    ->required(),
                            ])
                            ->defaultItems(0),
                    ]),

                    Forms\Components\Tabs\Tab::make('Капсулы')->schema([
                        Forms\Components\Repeater::make('forms.capsules')
                            ->label('Капсулы (дозировка/количество шт.)')
                            ->schema([
                                Forms\Components\TextInput::make('dose')
                                    ->label('Дозировка (мг)')
                                    ->numeric()
                                    ->required(),

                                Forms\Components\TextInput::make('quantity')
                                    ->label('Количество в упаковке (шт)')
                                    ->numeric()
                                    ->required(),
                            ])
                            ->defaultItems(0),
                    ]),

                    Forms\Components\Tabs\Tab::make('Ампулы')->schema([
                        Forms\Components\Repeater::make('forms.ampules')
                            ->label('Ампулы (дозировка/объём мл/количество шт.)')
                            ->schema([
                                Forms\Components\TextInput::make('dose')
                                    ->label('Дозировка (мг)')
                                    ->numeric()
                                    ->required(),

                                Forms\Components\TextInput::make('volume')
                                    ->label('Объём (мл)')
                                    ->numeric()
                                    ->required(),

                                Forms\Components\TextInput::make('quantity')
                                    ->label('Количество в упаковке (шт)')
                                    ->numeric()
                                    ->required(),
                            ])
                            ->defaultItems(0),
                    ]),
                ])->columnSpanFull(),

                Forms\Components\Toggle::make('preferential')->label('Льготный'),
                Forms\Components\Toggle::make('strict')->label('Строгий'),
                Forms\Components\Textarea::make('description')->label('Описание')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Название')->searchable(),
                Tables\Columns\TextColumn::make('group')
                    ->label('Группа')
                    ->formatStateUsing(fn ($state) => MedicineTypesEnum::getMatched((int)$state))
                    ->searchable(),
                Tables\Columns\TextColumn::make('latin_name')->label('Латинское название')->searchable(),
                Tables\Columns\IconColumn::make('preferential')->label('Льготный')->boolean(),
                Tables\Columns\IconColumn::make('strict')->label('Строгий')->boolean(),
            ])
            ->filters([])
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
            'index' => Pages\ListDrugs::route('/'),
            'create' => Pages\CreateDrug::route('/create'),
            'edit' => Pages\EditDrug::route('/{record}/edit'),
        ];
    }
}
