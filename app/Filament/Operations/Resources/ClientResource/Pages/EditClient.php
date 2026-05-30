<?php

namespace App\Filament\Operations\Resources\ClientResource\Pages;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Client\Query\GetAdminClientDetailQuery;
use App\Filament\Operations\Resources\ClientResource;
use App\Filament\Operations\Support\FilamentClientFormMapper;
use App\Filament\Operations\Support\ResolvesOperationsEditRecord;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditClient extends EditRecord
{
    use ResolvesOperationsEditRecord;

    protected static string $resource = ClientResource::class;

    protected static ?string $title = 'Клиент';

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        try {
            $payload = app(GetAdminClientDetailQuery::class)->execute((int) $this->getRecord()->getKey());
        } catch (ApiException $exception) {
            abort(404, $exception->getMessage());
        }

        return FilamentClientFormMapper::toFormState($payload);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return $record;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return null;
    }

    protected function getFormActions(): array
    {
        return [];
    }
}
