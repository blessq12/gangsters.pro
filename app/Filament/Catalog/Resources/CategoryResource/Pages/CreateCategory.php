<?php

namespace App\Filament\Catalog\Resources\CategoryResource\Pages;

use App\Application\Catalog\Command\CreateCategoryUseCase;
use App\Application\Common\Exceptions\ApiException;
use App\Filament\Catalog\Resources\CategoryResource;
use App\Filament\Catalog\Support\FilamentCategoryFormMapper;
use App\Infrastructure\Category\Model\PRD_Category;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected static ?string $title = 'Новая категория';

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        try {
            $detail = app(CreateCategoryUseCase::class)->execute(
                FilamentCategoryFormMapper::toCreateDto($data),
            );
        } catch (ApiException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();

            throw new Halt();
        }

        return PRD_Category::query()->findOrFail($detail['category']['id']);
    }
}
