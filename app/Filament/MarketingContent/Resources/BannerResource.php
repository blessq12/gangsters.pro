<?php

namespace App\Filament\MarketingContent\Resources;

use App\Filament\MarketingContent\Concerns\HasMarketingHubIndexUrl;
use App\Filament\MarketingContent\Resources\BannerResource\Pages\CreateBanner;
use App\Filament\MarketingContent\Resources\BannerResource\Pages\EditBanner;
use App\Filament\MarketingContent\Resources\BannerResource\Schemas\BannerForm;
use App\Infrastructure\MarketingContent\Model\MKT_Banner;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BannerResource extends Resource
{
    use HasMarketingHubIndexUrl;

    protected static ?string $model = MKT_Banner::class;

    protected static ?string $slug = 'marketing/banners';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $modelLabel = 'Баннер';

    protected static ?string $pluralModelLabel = 'Баннеры';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return BannerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table;
    }

    public static function getPages(): array
    {
        return [
            'create' => CreateBanner::route('/create'),
            'edit' => EditBanner::route('/{record}/edit'),
        ];
    }

    protected static function marketingHubTab(): string
    {
        return 'banners';
    }
}
