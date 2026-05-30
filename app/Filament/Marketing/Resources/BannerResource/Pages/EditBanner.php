<?php

namespace App\Filament\Marketing\Resources\BannerResource\Pages;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Marketing\Banner\Command\DeleteBannerUseCase;
use App\Application\Marketing\Banner\Command\SaveBannerUseCase;
use App\Application\Marketing\Banner\Query\GetAdminBannerDetailQuery;
use App\Filament\Marketing\Concerns\ResolvesMarketingBannerUploads;
use App\Filament\Marketing\Pages\ManageMarketing;
use App\Filament\Marketing\Resources\BannerResource;
use App\Filament\Marketing\Support\FilamentBannerFormMapper;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;

class EditBanner extends EditRecord
{
    use ResolvesMarketingBannerUploads;

    protected static string $resource = BannerResource::class;

    /**
     * @var array{image_mobile: ?string, image_desktop: ?string}
     */
    protected array $existingImagePaths = [
        'image_mobile' => null,
        'image_desktop' => null,
    ];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->action(function (): void {
                    try {
                        app(DeleteBannerUseCase::class)->execute((int) $this->getRecord()->getKey());
                        Notification::make()->title('Баннер удалён')->success()->send();
                        $this->redirect(ManageMarketing::getUrl(['tab' => 'banners']));
                    } catch (ApiException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();
                    }
                }),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $detail = app(GetAdminBannerDetailQuery::class)->execute((int) $this->getRecord()->getKey());
        $this->existingImagePaths = [
            'image_mobile' => $detail['image_mobile'] ?? $detail['image'] ?? null,
            'image_desktop' => $detail['image_desktop'] ?? $detail['image'] ?? null,
        ];

        return FilamentBannerFormMapper::toFormState($detail);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $paths = $this->resolveBannerImagePaths($data, $this->existingImagePaths);

        try {
            app(SaveBannerUseCase::class)->execute(
                FilamentBannerFormMapper::toSaveDto((int) $record->getKey(), $data, $paths),
            );
        } catch (ApiException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();

            throw new Halt;
        }

        return $record;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Баннер сохранён';
    }
}
