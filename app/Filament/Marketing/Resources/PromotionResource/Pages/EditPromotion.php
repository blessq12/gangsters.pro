<?php

namespace App\Filament\Marketing\Resources\PromotionResource\Pages;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Marketing\Promotion\Command\DeletePromotionUseCase;
use App\Application\Marketing\Promotion\Command\SavePromotionUseCase;
use App\Filament\Marketing\Concerns\ResolvesMarketingPromotionUploads;
use App\Filament\Marketing\Pages\ManageMarketing;
use App\Filament\Marketing\Resources\PromotionResource;
use App\Filament\Marketing\Support\FilamentPromotionFormMapper;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;

class EditPromotion extends EditRecord
{
    use ResolvesMarketingPromotionUploads;

    protected static string $resource = PromotionResource::class;

    protected ?string $existingImagePath = null;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->action(function (): void {
                    try {
                        app(DeletePromotionUseCase::class)->execute((int) $this->getRecord()->getKey());
                        Notification::make()->title('Акция удалена')->success()->send();
                        $this->redirect(ManageMarketing::getUrl(['tab' => 'promotions']));
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
        /** @var \App\Infrastructure\SystemContent\Model\SYS_Promotion $record */
        $record = $this->getRecord();
        $this->existingImagePath = $record->image;

        return FilamentPromotionFormMapper::toFormState($record);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $imagePath = $this->resolvePromotionImagePath($data, $this->existingImagePath);

        try {
            app(SavePromotionUseCase::class)->execute(
                FilamentPromotionFormMapper::toSaveDto((int) $record->getKey(), $data, $imagePath),
            );
        } catch (ApiException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();

            throw new Halt;
        }

        return $record;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Акция сохранена';
    }
}
