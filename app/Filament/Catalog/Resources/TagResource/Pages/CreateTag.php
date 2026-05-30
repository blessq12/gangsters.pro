<?php

namespace App\Filament\Catalog\Resources\TagResource\Pages;

use App\Application\Catalog\Command\CreateAdminTagUseCase;
use App\Application\Common\Exceptions\ApiException;
use App\Filament\Catalog\Resources\TagResource;
use App\Filament\Catalog\Support\FilamentTagFormMapper;
use App\Infrastructure\Product\Model\PRD_Tag;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;

class CreateTag extends CreateRecord
{
    protected static string $resource = TagResource::class;

    protected static ?string $title = 'Новый тег';

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        try {
            $detail = app(CreateAdminTagUseCase::class)->execute(
                FilamentTagFormMapper::toCreateDto($data),
            );
        } catch (ApiException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();

            throw new Halt();
        }

        return PRD_Tag::query()->findOrFail($detail['id']);
    }
}
