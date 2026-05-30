<?php

namespace App\Filament\Catalog\Resources\ProductResource\Pages;

use App\Application\Catalog\Command\CreateProductUseCase;
use App\Application\Common\Exceptions\ApiException;
use App\Filament\Catalog\Resources\ProductResource;
use App\Filament\Catalog\Support\FilamentProductFormMapper;
use App\Infrastructure\Product\Model\PRD_Product;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected static ?string $title = 'Новый товар';

    protected function fillForm(): void
    {
        $this->callHook('beforeFill');

        $this->form->fill(FilamentProductFormMapper::emptyFormState());

        $this->callHook('afterFill');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        try {
            $detail = app(CreateProductUseCase::class)->execute(
                FilamentProductFormMapper::toCreateDto($data),
            );
        } catch (ApiException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();

            throw new Halt();
        }

        return PRD_Product::query()->findOrFail($detail['id']);
    }
}
