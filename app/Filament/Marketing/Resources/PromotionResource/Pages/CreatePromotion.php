<?php

namespace App\Filament\Marketing\Resources\PromotionResource\Pages;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Marketing\Promotion\Command\SavePromotionUseCase;
use App\Filament\Marketing\Concerns\ResolvesMarketingPromotionUploads;
use App\Filament\Marketing\Resources\PromotionResource;
use App\Filament\Marketing\Support\FilamentPromotionFormMapper;
use App\Infrastructure\SystemContent\Model\SYS_Promotion;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;

class CreatePromotion extends CreateRecord
{
    use ResolvesMarketingPromotionUploads;

    protected static string $resource = PromotionResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $imagePath = $this->resolvePromotionImagePath($data, null);

        try {
            $saved = app(SavePromotionUseCase::class)->execute(
                FilamentPromotionFormMapper::toSaveDto(0, $data, $imagePath),
            );
        } catch (ApiException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();

            throw new Halt;
        }

        return SYS_Promotion::query()->findOrFail($saved['id']);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Акция создана';
    }
}
