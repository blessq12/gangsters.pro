<?php

namespace App\Filament\Content\MarketingContent\Resources\BannerResource\Pages;

use App\Filament\Content\MarketingContent\Concerns\PreservesMarketingMediaOnEmptyUpload;
use App\Filament\Content\MarketingContent\Concerns\RedirectsToMarketingHub;
use App\Filament\Content\MarketingContent\Resources\BannerResource;
use Filament\Resources\Pages\EditRecord;

class EditBanner extends EditRecord
{
    use PreservesMarketingMediaOnEmptyUpload;
    use RedirectsToMarketingHub;

    protected static string $resource = BannerResource::class;

    protected static ?string $title = 'Редактирование баннера';

    protected static function marketingHubTab(): string
    {
        return 'banners';
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->preserveMarketingMediaPaths($data, ['image_desktop', 'image_mobile']);
    }
}
