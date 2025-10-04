<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Jobs\BalanceTransactionJob;
use App\Models\User;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Log;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Пользователи';

    protected static ?string $navigationGroup = 'Пользователи';

    protected static ?string $modelLabel = 'пользователя';

    protected static ?string $pluralModelLabel = 'пользователи';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Основная информация')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('login')
                                    ->label('Логин')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('password')
                                    ->label('Пароль')
                                    ->password()
                                    ->confirmed()
                                    ->autocomplete('new-password')
                                    ->required(fn(string $operation): bool => $operation === 'create')
                                    ->dehydrated(fn(?string $state): bool => filled($state)),

                                Forms\Components\TextInput::make('password_confirmation')
                                    ->label('Подтвердите пароль')
                                    ->password()
                                    ->autocomplete('new-password')
                                    ->required(fn(string $operation): bool => $operation === 'create')
                                    ->dehydrated(false),

                                Forms\Components\TextInput::make('balance')
                                    ->label('Баланс')
                                    ->numeric()
                                    ->default(0),
                            ]),
                    ]),

                Section::make('Дополнительно')
                    ->collapsed() // сворачивается по умолчанию
                    ->schema([
                        Select::make('roles')
                            ->label('Роли')
                            ->multiple()
                            ->preload()
                            ->relationship('roles', 'name') // автоматически работает со Spatie
                            ->searchable()
                            ->columnSpanFull(),

                        FileUpload::make('avatar')
                            ->label('Аватар')
                            ->image()
                            ->directory('avatars')
                            ->disk('public')
                            ->visibility('public')
                            ->maxSize(2048),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('login')->label('Логин')->searchable(),
                TextColumn::make('email')->label('Email')->searchable(),
                TextColumn::make('balance')->label('Баланс')->money('RUB'),
                TextColumn::make('created_at')->label('Создано')->dateTime(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Action::make('balanceHistory')
                    ->label('История транзакций')
                    ->action(fn(User $record) => static::showBalanceHistory($record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function showBalanceHistory(User $user): void
    {
        redirect()->to(
            BalanceTransactionResource::getUrl('index', [
                'tableFilters[user_id][value]' => $user->id,
            ])
        );
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function beforeCreate($record, $data): void
    {
        $record->password = Hash::make($data['password']);
    }
}
