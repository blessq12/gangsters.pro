<?php

namespace App\Filament\Content\MarketingContent\Resources\PromotionResource\Pages;

use App\Filament\Content\MarketingContent\Concerns\PreservesMarketingMediaOnEmptyUpload;
use App\Filament\Content\MarketingContent\Concerns\RedirectsToMarketingHub;
use App\Filament\Content\MarketingContent\Resources\PromotionResource;
use Filament\Resources\Pages\EditRecord;

class EditPromotion extends EditRecord
{
    use PreservesMarketingMediaOnEmptyUpload;
    use RedirectsToMarketingHub;

    protected static string $resource = PromotionResource::class;

    protected static ?string $title = 'Редактирование акции';

    protected static function marketingHubTab(): string
    {
        return 'promotions';
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->preserveMarketingMediaPaths($data, ['image']);
    }
}
