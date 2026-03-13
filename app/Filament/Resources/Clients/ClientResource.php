<?php

namespace App\Filament\Resources\Clients;

use App\Filament\Resources\Clients\Pages\CreateClient;
use App\Filament\Resources\Clients\Pages\EditClient;
use App\Filament\Resources\Clients\Pages\ListClients;
use App\Infrastructure\Client\Model\UR_Client;
use BackedEnum;
use UnitEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClientResource extends Resource
{
    protected static ?string $model = UR_Client::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;

    protected static ?string $navigationLabel = 'Клиенты';

    protected static string|UnitEnum|null $navigationGroup = 'Пользователи';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Имя')
                ->required(),
            TextInput::make('phone')
                ->label('Телефон')
                ->required(),
            TextInput::make('email')
                ->label('Email')
                ->email()
                ->nullable(),
            DatePicker::make('birth_date')
                ->label('Дата рождения')
                ->nullable(),
            Select::make('status')
                ->label('Статус')
                ->options([
                    'active' => 'Активен',
                    'blocked' => 'Заблокирован',
                ])
                ->required()
                ->default('active'),
            Toggle::make('consent_personal_data')
                ->label('Согласие на обработку данных'),
            Toggle::make('consent_marketing')
                ->label('Согласие на маркетинг'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Имя')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),
                IconColumn::make('consent_personal_data')
                    ->label('Персональные данные')
                    ->boolean(),
                IconColumn::make('consent_marketing')
                    ->label('Маркетинг')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\Clients\RelationManagers\OrdersRelationManager::class,
            \App\Filament\Resources\Clients\RelationManagers\AddressesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClients::route('/'),
            'create' => CreateClient::route('/create'),
            'edit' => EditClient::route('/{record}/edit'),
        ];
    }
}

