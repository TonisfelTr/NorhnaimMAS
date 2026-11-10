<?php

namespace App\Filament\Resources;

use App\Enums\MedicineTypesEnum;
use App\Filament\Resources\DrugResource\Pages;
use App\Filament\Resources\DrugResource\Support\DrugFormData;
use App\Models\Drug;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

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
                Forms\Components\Section::make('Основные сведения')
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
                            ->label('Группа препарата')
                            ->options(collect(MedicineTypesEnum::cases())->mapWithKeys(fn ($type) => [
                                $type->value => MedicineTypesEnum::getMatched((int) $type->value),
                            ])->all())
                            ->searchable()
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Fieldset::make('Период полувыведения (ч)')
                    ->schema([
                        Forms\Components\TextInput::make('ht_output_from')
                            ->label('От')
                            ->numeric()
                            ->minValue(0)
                            ->step('0.1'),

                        Forms\Components\TextInput::make('ht_output_to')
                            ->label('До')
                            ->numeric()
                            ->minValue(0)
                            ->step('0.1'),
                    ])
                    ->columns(2),

                Forms\Components\Tabs::make('Параметры препарата')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Таблетки')
                            ->schema([
                                Forms\Components\Repeater::make('forms_tablets')
                                    ->label('Таблетки (дозировка / количество в упаковке)')
                                    ->schema([
                                        Forms\Components\TextInput::make('dose')
                                            ->label('Дозировка (мг)')
                                            ->numeric()
                                            ->minValue(0.01)
                                            ->step('0.01')
                                            ->required(),
                                        Forms\Components\TextInput::make('quantity')
                                            ->label('Количество в упаковке (шт.)')
                                            ->numeric()
                                            ->step(1)
                                            ->minValue(1)
                                            ->required(),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(0)
                                    ->addActionLabel('Добавить дозировку / фасовку')
                                    ->helperText('Для одной дозировки с несколькими фасовками добавляй отдельную строку на каждое количество. При сохранении одинаковые дозировки будут объединены.'),
                            ]),

                        Forms\Components\Tabs\Tab::make('Драже')
                            ->schema([
                                Forms\Components\Repeater::make('forms_dragees')
                                    ->label('Драже (дозировка / количество в упаковке)')
                                    ->schema([
                                        Forms\Components\TextInput::make('dose')
                                            ->label('Дозировка (мг)')
                                            ->numeric()
                                            ->minValue(0.01)
                                            ->step('0.01')
                                            ->required(),
                                        Forms\Components\TextInput::make('quantity')
                                            ->label('Количество в упаковке (шт.)')
                                            ->numeric()
                                            ->step(1)
                                            ->minValue(1)
                                            ->required(),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(0)
                                    ->addActionLabel('Добавить дозировку / фасовку'),
                            ]),

                        Forms\Components\Tabs\Tab::make('Капсулы')
                            ->schema([
                                Forms\Components\Repeater::make('forms_capsules')
                                    ->label('Капсулы (дозировка / количество в упаковке)')
                                    ->schema([
                                        Forms\Components\TextInput::make('dose')
                                            ->label('Дозировка (мг)')
                                            ->numeric()
                                            ->minValue(0.01)
                                            ->step('0.01')
                                            ->required(),
                                        Forms\Components\TextInput::make('quantity')
                                            ->label('Количество в упаковке (шт.)')
                                            ->numeric()
                                            ->step(1)
                                            ->minValue(1)
                                            ->required(),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(0)
                                    ->addActionLabel('Добавить дозировку / фасовку'),
                            ]),

                        Forms\Components\Tabs\Tab::make('Ампулы')
                            ->schema([
                                Forms\Components\Repeater::make('forms_ampules')
                                    ->label('Ампулы (дозировка / объём / количество в упаковке)')
                                    ->schema([
                                        Forms\Components\TextInput::make('dose')
                                            ->label('Дозировка (мг)')
                                            ->numeric()
                                            ->minValue(0.01)
                                            ->step('0.01')
                                            ->required(),
                                        Forms\Components\TextInput::make('volume')
                                            ->label('Объём ампулы (мл)')
                                            ->numeric()
                                            ->minValue(0.01)
                                            ->step('0.01')
                                            ->required(),
                                        Forms\Components\TextInput::make('quantity')
                                            ->label('Количество в упаковке (шт.)')
                                            ->numeric()
                                            ->step(1)
                                            ->minValue(1)
                                            ->required(),
                                    ])
                                    ->columns(3)
                                    ->defaultItems(0)
                                    ->addActionLabel('Добавить дозировку / объём / фасовку')
                                    ->helperText('Объём ампулы обязателен: он сохраняется отдельным уровнем в JSON forms.'),
                            ]),

                        Forms\Components\Tabs\Tab::make('Показания')
                            ->schema([
                                Forms\Components\Repeater::make('indications_data')
                                    ->label('Показания по МКБ-10')
                                    ->schema([
                                        Forms\Components\Select::make('diagnose_id')
                                            ->label('Диагноз')
                                            ->searchable()
                                            ->required()
                                            ->getSearchResultsUsing(fn (string $search): array => DrugFormData::diagnosisSearchResults($search))
                                            ->getOptionLabelUsing(fn ($value): ?string => DrugFormData::diagnosisOptionLabel($value)),

                                        Forms\Components\Toggle::make('indicated_in_russia')
                                            ->label('По рекомендациям Минздрава РФ')
                                            ->default(false),

                                        Forms\Components\Toggle::make('indicated_by_fda')
                                            ->label('По рекомендациям FDA')
                                            ->default(false),
                                    ])
                                    ->columns(3)
                                    ->defaultItems(0)
                                    ->addActionLabel('Добавить показание')
                                    ->reorderable()
                                    ->helperText('Для каждого диагноза отдельно отметь источники. Если оба флага выключены, строка не будет сохранена.'),
                            ]),

                        Forms\Components\Tabs\Tab::make('Противопоказания')
                            ->schema([
                                Forms\Components\Select::make('contraindications_ids')
                                    ->label('Противопоказания')
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->options(fn (): array => DrugFormData::contraindicationOptions())
                                    ->helperText('Выбирай противопоказания из общего справочника. Новые значения добавляются в справочник противопоказаний.'),
                            ]),

                        Forms\Components\Tabs\Tab::make('Особые условия')
                            ->schema([
                                Forms\Components\Toggle::make('preferential')
                                    ->label('Доступен по льготе'),

                                Forms\Components\Toggle::make('strict')
                                    ->label('Строгий учёт')
                                    ->helperText('Включено — рецептурный бланк № 148-1/у-88. Выключено — № 107-1/у.'),

                                Forms\Components\Toggle::make('pregnancy')
                                    ->label('Есть ограничения при беременности'),

                                Forms\Components\Toggle::make('lactation')
                                    ->label('Есть ограничения при грудном вскармливании'),

                                Forms\Components\Toggle::make('liver')
                                    ->label('Есть ограничения при нарушении функции печени'),

                                Forms\Components\Toggle::make('kidneys')
                                    ->label('Есть ограничения при нарушении функции почек'),
                            ])
                            ->columns(2),

                        Forms\Components\Tabs\Tab::make('Описание')
                            ->schema([
                                Forms\Components\Textarea::make('description')
                                    ->label('Описание и дополнительные сведения')
                                    ->rows(6)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('group')
                    ->label('Группа препарата')
                    ->formatStateUsing(fn ($state) => MedicineTypesEnum::getMatched((int) $state))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('forms_display')
                    ->label('Формы')
                    ->getStateUsing(fn (Drug $record) => DrugFormData::formsHtml($record))
                    ->html()
                    ->wrap(),

                Tables\Columns\TextColumn::make('prescription_type')
                    ->label('Тип рецепта')
                    ->getStateUsing(fn (Drug $record) => $record->strict ? '№ 148-1/у-88' : '№ 107-1/у')
                    ->badge()
                    ->color(fn ($state) => $state === '№ 148-1/у-88' ? 'warning' : 'gray'),

                Tables\Columns\TextColumn::make('indications_display')
                    ->label('Показания')
                    ->getStateUsing(fn (Drug $record) => DrugFormData::indicationsHtml($record))
                    ->html()
                    ->wrap(),

                Tables\Columns\TextColumn::make('contraindications_display')
                    ->label('Противопоказания')
                    ->getStateUsing(fn (Drug $record) => DrugFormData::contraindicationsHtml($record))
                    ->html()
                    ->wrap(),

                Tables\Columns\IconColumn::make('preferential')
                    ->label('Доступен по льготе')
                    ->boolean(),
            ])
            ->defaultSort('name')
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
