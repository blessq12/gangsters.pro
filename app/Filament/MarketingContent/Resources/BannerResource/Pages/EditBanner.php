<?php

namespace App\Filament\MarketingContent\Resources\BannerResource\Pages;

use App\Filament\MarketingContent\Concerns\PreservesMarketingMediaOnEmptyUpload;
use App\Filament\MarketingContent\Concerns\RedirectsToMarketingHub;
use App\Filament\MarketingContent\Resources\BannerResource;
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
