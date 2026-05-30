<?php

namespace App\Filament\Catalog\Resources\TagResource\Pages;

use App\Application\Catalog\Command\UpdateAdminTagUseCase;
use App\Application\Catalog\Query\GetAdminTagDetailQuery;
use App\Application\Common\Exceptions\ApiException;
use App\Filament\Catalog\Resources\TagResource;
use App\Filament\Catalog\Support\FilamentTagFormMapper;
use App\Filament\Catalog\Support\ResolvesCatalogEditRecord;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;

class EditTag extends EditRecord
{
    use ResolvesCatalogEditRecord;

    protected static string $resource = TagResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $tag = app(GetAdminTagDetailQuery::class)->execute((int) $this->getRecord()->getKey());

        return FilamentTagFormMapper::toFormState($tag);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            app(UpdateAdminTagUseCase::class)->execute(
                FilamentTagFormMapper::toUpdateDto((int) $record->getKey(), $data),
            );
        } catch (ApiException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();

            throw new Halt();
        }

        $this->refreshRecordFormState();

        return $record;
    }
}
