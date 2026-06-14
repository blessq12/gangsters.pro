<?php

namespace App\Filament\Client\Resources;

use App\Filament\Client\Resources\Pages\ListClients;
use App\Filament\Client\Resources\Pages\ViewClient;
use App\Filament\Client\Resources\Schemas\ClientViewSchema;
use App\Filament\Client\Resources\Tables\ClientsTable;
use App\Infrastructure\Client\Model\CLN_Client;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ClientResource extends Resource
{
    protected static ?string $model = CLN_Client::class;

    protected static ?string $navigationLabel = 'Клиенты';

    protected static ?string $slug = 'clients';

    protected static ?string $modelLabel = 'Клиент';

    protected static ?string $pluralModelLabel = 'Клиенты';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?int $navigationSort = 26;

    public static function form(Schema $schema): Schema
    {
        return ClientViewSchema::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClients::route('/'),
            'view' => ViewClient::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
