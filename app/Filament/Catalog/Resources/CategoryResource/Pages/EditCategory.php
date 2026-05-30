<?php

namespace App\Filament\Catalog\Resources\CategoryResource\Pages;

use App\Application\Catalog\Command\ActivateCategoryUseCase;
use App\Application\Catalog\Command\DeactivateCategoryUseCase;
use App\Application\Catalog\Command\UpdateCategoryUseCase;
use App\Application\Catalog\Query\GetAdminCategoryDetailQuery;
use App\Application\Common\Exceptions\ApiException;
use App\Filament\Catalog\Resources\CategoryResource;
use App\Filament\Catalog\Support\FilamentCategoryFormMapper;
use App\Filament\Catalog\Support\ResolvesCatalogEditRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;

class EditCategory extends EditRecord
{
    use ResolvesCatalogEditRecord;

    protected static string $resource = CategoryResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $detail = app(GetAdminCategoryDetailQuery::class)->execute((int) $this->getRecord()->getKey());

        return FilamentCategoryFormMapper::toFormState($detail);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('deactivate')
                ->label('Скрыть')
                ->color('warning')
                ->visible(fn (): bool => (bool) $this->getRecord()->is_active)
                ->action(fn () => $this->toggleActive(false)),
            Action::make('activate')
                ->label('Показать')
                ->color('success')
                ->visible(fn (): bool => ! (bool) $this->getRecord()->is_active)
                ->action(fn () => $this->toggleActive(true)),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            app(UpdateCategoryUseCase::class)->execute(
                FilamentCategoryFormMapper::toUpdateDto((int) $record->getKey(), $data),
            );
        } catch (ApiException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();

            throw new Halt();
        }

        $this->refreshRecordFormState();

        return $record;
    }

    private function toggleActive(bool $active): void
    {
        $categoryId = (int) $this->getRecord()->getKey();

        try {
            if ($active) {
                app(ActivateCategoryUseCase::class)->execute($categoryId);
            } else {
                app(DeactivateCategoryUseCase::class)->execute($categoryId);
            }

            Notification::make()->title('Сохранено')->success()->send();
            $this->refreshRecordFormState();
        } catch (ApiException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();
        }
    }
}
