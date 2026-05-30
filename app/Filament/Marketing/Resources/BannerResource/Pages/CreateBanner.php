<?php

namespace App\Filament\Marketing\Resources\BannerResource\Pages;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Marketing\Banner\Command\SaveBannerUseCase;
use App\Filament\Marketing\Concerns\ResolvesMarketingBannerUploads;
use App\Filament\Marketing\Resources\BannerResource;
use App\Filament\Marketing\Support\FilamentBannerFormMapper;
use App\Infrastructure\SystemContent\Model\SYS_Banner;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;

class CreateBanner extends CreateRecord
{
    use ResolvesMarketingBannerUploads;

    protected static string $resource = BannerResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $paths = $this->resolveBannerImagePaths($data, [
            'image_mobile' => null,
            'image_desktop' => null,
        ]);

        try {
            $saved = app(SaveBannerUseCase::class)->execute(
                FilamentBannerFormMapper::toSaveDto(0, $data, $paths),
            );
        } catch (ApiException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();

            throw new Halt;
        }

        return SYS_Banner::query()->findOrFail($saved['id']);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Баннер создан';
    }
}
