<?php

namespace App\Filament\Catalog\Resources\ProductResource\Pages;

use App\Application\Catalog\Command\ActivateProductUseCase;
use App\Application\Catalog\Command\ArchiveProductUseCase;
use App\Application\Catalog\Command\DeleteProductImageUseCase;
use App\Application\Catalog\Command\DeleteProductUseCase;
use App\Application\Catalog\Command\SyncProductTagsUseCase;
use App\Application\Operations\CartRules\Contracts\UpdateProductCartRuleFlagsContract;
use App\Application\Catalog\Command\UpdateProductUseCase;
use App\Application\Catalog\Command\UploadProductImageUseCase;
use App\Application\Catalog\Query\GetAdminProductFormQuery;
use App\Application\Common\Exceptions\ApiException;
use App\Domain\Product\Entity\Product;
use App\Filament\Catalog\Pages\ManageCatalog;
use App\Filament\Catalog\Resources\ProductResource;
use App\Filament\Catalog\Support\FilamentProductFormMapper;
use App\Filament\Catalog\Support\ResolvesCatalogEditRecord;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class EditProduct extends EditRecord
{
    use ResolvesCatalogEditRecord;

    protected static string $resource = ProductResource::class;

    protected ?int $imagesCount = null;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('archive')
                ->label('В архив')
                ->color('warning')
                ->visible(fn (): bool => $this->getRecord()->status !== Product::STATUS_ARCHIVED)
                ->action(function (): void {
                    $this->runStatusChange(fn () => app(ArchiveProductUseCase::class)->execute((int) $this->getRecord()->getKey()));
                }),
            Action::make('activate')
                ->label('Активировать')
                ->color('success')
                ->visible(fn (): bool => $this->getRecord()->status === Product::STATUS_ARCHIVED)
                ->action(function (): void {
                    $this->runStatusChange(fn () => app(ActivateProductUseCase::class)->execute((int) $this->getRecord()->getKey()));
                }),
            Action::make('deleteImage')
                ->label('Удалить изображение')
                ->color('danger')
                ->visible(fn (): bool => ($this->imagesCount ?? 0) > 0)
                ->form([
                    Select::make('image_index')
                        ->label('Изображение')
                        ->options(fn (): array => $this->imageIndexOptions())
                        ->required()
                        ->native(false),
                ])
                ->action(function (array $data): void {
                    $productId = (int) $this->getRecord()->getKey();

                    try {
                        app(DeleteProductImageUseCase::class)->execute(
                            $productId,
                            (int) $data['image_index'],
                        );
                        Notification::make()->title('Изображение удалено')->success()->send();
                        $this->refreshRecordFormState();
                    } catch (ApiException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();
                    }
                }),
            DeleteAction::make()
                ->action(function (): void {
                    try {
                        app(DeleteProductUseCase::class)->execute((int) $this->getRecord()->getKey());
                        Notification::make()->title('Товар удалён')->success()->send();
                        $this->redirect(ManageCatalog::getUrl(['tab' => 'products']));
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
        $detail = app(GetAdminProductFormQuery::class)->execute((int) $this->getRecord()->getKey());
        $this->imagesCount = (int) ($detail['images_count'] ?? 0);

        return FilamentProductFormMapper::toFormState($detail);
    }

    /**
     * @return array<int, string>
     */
    private function imageIndexOptions(): array
    {
        $options = [];
        $count = $this->imagesCount ?? 0;

        for ($i = 0; $i < $count; $i++) {
            $options[$i] = 'Изображение #'.($i + 1);
        }

        return $options;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $productId = (int) $record->getKey();

        try {
            app(UpdateProductUseCase::class)->execute(
                FilamentProductFormMapper::toUpdateDto($productId, $data),
            );
            app(SyncProductTagsUseCase::class)->execute(
                FilamentProductFormMapper::toSyncTagsDto($productId, $data),
            );
            app(UpdateProductCartRuleFlagsContract::class)->execute(
                FilamentProductFormMapper::toCartRuleFlagsDto($productId, $data),
            );

            $upload = $data['image_upload'] ?? null;
            if ($upload instanceof TemporaryUploadedFile) {
                app(UploadProductImageUseCase::class)->execute($productId, $upload);
            }
        } catch (ApiException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();

            throw new Halt();
        }

        $this->refreshRecordFormState();

        return $record;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Товар сохранён';
    }

    /**
     * @param  callable(): void  $callback
     */
    private function runStatusChange(callable $callback): void
    {
        try {
            $callback();
            Notification::make()->title('Статус обновлён')->success()->send();
            $this->refreshRecordFormState();
        } catch (ApiException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();
        }
    }
}
