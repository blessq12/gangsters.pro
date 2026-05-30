<?php

namespace App\Filament\Marketing\Resources;

use App\Filament\Marketing\Resources\PromotionResource\Pages\CreatePromotion;
use App\Filament\Marketing\Resources\PromotionResource\Pages\EditPromotion;
use App\Filament\Marketing\Schemas\PromotionForm;
use App\Filament\Marketing\Support\RedirectsMarketingIndexToHub;
use App\Infrastructure\SystemContent\Model\SYS_Promotion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PromotionResource extends Resource
{
    use RedirectsMarketingIndexToHub;

    protected static string $marketingHubTab = 'promotions';

    protected static ?string $model = SYS_Promotion::class;

    protected static ?string $slug = 'marketing/promotions';

    protected static ?string $modelLabel = 'акция';

    protected static ?string $pluralModelLabel = 'акции';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return PromotionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table;
    }

    public static function getPages(): array
    {
        return [
            'create' => CreatePromotion::route('/create'),
            'edit' => EditPromotion::route('/{record}/edit'),
        ];
    }
}
