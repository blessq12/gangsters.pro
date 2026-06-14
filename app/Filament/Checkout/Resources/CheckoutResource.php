<?php

namespace App\Filament\Checkout\Resources;

use App\Filament\Checkout\Resources\Pages\ListCheckouts;
use App\Filament\Checkout\Resources\Pages\ViewCheckout;
use App\Filament\Checkout\Resources\Schemas\CheckoutViewSchema;
use App\Filament\Checkout\Resources\Tables\CheckoutsTable;
use App\Infrastructure\Checkout\Model\CHK_Checkout;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CheckoutResource extends Resource
{
    protected static ?string $model = CHK_Checkout::class;

    protected static ?string $navigationLabel = 'Оформления';

    protected static ?string $slug = 'checkouts';

    protected static ?string $modelLabel = 'Оформление';

    protected static ?string $pluralModelLabel = 'Оформления';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?int $navigationSort = 25;

    public static function form(Schema $schema): Schema
    {
        return CheckoutViewSchema::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CheckoutsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCheckouts::route('/'),
            'view' => ViewCheckout::route('/{record}'),
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
