<?php

namespace App\Filament\MarketingContent\Resources;

use App\Filament\MarketingContent\Resources\MarketingContentResource\Pages\ManageMarketingContent;
use App\Filament\Support\AdminNavigationGroup;
use App\Infrastructure\MarketingContent\Model\MKT_Banner;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MarketingContentResource extends Resource
{
    protected static ?string $model = MKT_Banner::class;

    protected static ?string $navigationLabel = 'Маркетинг';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static ?string $slug = 'marketing';

    protected static ?string $modelLabel = 'Маркетинг';

    protected static ?string $pluralModelLabel = 'Маркетинг';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 20;

    protected static string | \UnitEnum | null $navigationGroup = AdminNavigationGroup::Storefront;

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMarketingContent::route('/'),
        ];
    }
}
