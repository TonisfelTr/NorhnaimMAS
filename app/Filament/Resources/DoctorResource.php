<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DoctorResource\Pages;
use App\Models\Doctor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DoctorResource extends Resource
{
    protected static ?string $model = Doctor::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Пользователи';
    protected static ?string $navigationLabel = 'Врачи';
    protected static ?string $pluralLabel = 'Врачи';
    protected static ?string $modelLabel = 'Врач';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Основная информация')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('surname')->required()->label('Фамилия'),
                    Forms\Components\TextInput::make('name')->required()->label('Имя'),
                    Forms\Components\TextInput::make('patronym')->label('Отчество'),
                    Forms\Components\DatePicker::make('birth_at')->required()->label('Дата рождения'),
                    Forms\Components\TextInput::make('status')->default('Психиатр')->required()->label('Специализация'),
                    Forms\Components\TextInput::make('city')->label('Город'),
                ]),

            Forms\Components\Section::make('Место работы')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('clinic_id')
                        ->relationship('clinic', 'name')
                        ->label('Клиника'),
                    Forms\Components\Textarea::make('address_job')
                        ->required()
                        ->label('Адрес работы'),
                ]),

            Forms\Components\Section::make('Дополнительная информация')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('experience_years')->numeric()->label('Опыт работы (лет)'),
                    Forms\Components\TextInput::make('experience_months')->numeric()->label('Опыт работы (месяцев)'),
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'login')
                        ->searchable()
                        ->label('Привязанный пользователь'),
                    Forms\Components\SpatieMediaLibraryFileUpload::make('photo')
                        ->collection('photos')
                        ->image()
                        ->label('Фотография'),
                    Forms\Components\Textarea::make('about')
                        ->columnSpanFull()
                        ->label('Информация о враче'),
                ]),

            Forms\Components\Section::make('Прайс-лист услуг врача')
                ->schema([
                    Forms\Components\Repeater::make('pricelists')
                        ->relationship()
                        ->schema([
                            Forms\Components\TextInput::make('name')->required()->label('Название услуги'),
                            Forms\Components\TextInput::make('group')->label('Группа услуг'),
                            Forms\Components\TextInput::make('price')->numeric()->required()->label('Цена (₽)'),
                            Forms\Components\TextInput::make('discount_price')->numeric()->label('Цена со скидкой (₽)'),
                        ])
                        ->defaultItems(0),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('photo_url')->label('Фотография'),
            Tables\Columns\TextColumn::make('surname')->searchable()->sortable()->label('Фамилия'),
            Tables\Columns\TextColumn::make('name')->searchable()->sortable()->label('Имя'),
            Tables\Columns\TextColumn::make('status')->sortable()->label('Специализация'),
            Tables\Columns\TextColumn::make('city')->searchable()->sortable()->label('Город'),
            Tables\Columns\TextColumn::make('clinic.name')->sortable()->label('Клиника'),
            Tables\Columns\TextColumn::make('created_at')->date('d.m.Y')->sortable()->label('Добавлен'),
        ])
            ->filters([
                Tables\Filters\SelectFilter::make('city')
                    ->label('Город')
                    ->options(Doctor::query()->distinct()->pluck('city', 'city')),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Специализация')
                    ->options(Doctor::query()->distinct()->pluck('status', 'status')),
                Tables\Filters\SelectFilter::make('clinic_id')
                    ->label('Клиника')
                    ->relationship('clinic', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Редактировать'),
                Tables\Actions\DeleteAction::make()->label('Удалить'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Удалить выбранные'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDoctors::route('/'),
            'create' => Pages\CreateDoctor::route('/create'),
            'edit'   => Pages\EditDoctor::route('/{record}/edit'),
        ];
    }
}
