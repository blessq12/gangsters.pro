<?php

namespace App\Filament\MarketingContent\Resources;

use App\Filament\MarketingContent\Concerns\HasMarketingHubIndexUrl;
use App\Filament\MarketingContent\Resources\PromotionResource\Pages\CreatePromotion;
use App\Filament\MarketingContent\Resources\PromotionResource\Pages\EditPromotion;
use App\Filament\MarketingContent\Resources\PromotionResource\Schemas\PromotionForm;
use App\Infrastructure\Content\Model\MKT_Promotion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PromotionResource extends Resource
{
    use HasMarketingHubIndexUrl;

    protected static ?string $model = MKT_Promotion::class;

    protected static ?string $slug = 'marketing/promotions';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $modelLabel = 'Акция';

    protected static ?string $pluralModelLabel = 'Акции';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

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

    protected static function marketingHubTab(): string
    {
        return 'promotions';
    }
}
