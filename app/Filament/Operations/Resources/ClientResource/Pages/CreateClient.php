<?php

namespace App\Filament\Operations\Resources\ClientResource\Pages;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Client\Command\CreateAdminClientUseCase;
use App\Filament\Operations\Resources\ClientResource;
use App\Filament\Operations\Resources\ClientResource\Schemas\AdminClientCreateForm;
use App\Filament\Operations\Support\FilamentClientFormMapper;
use App\Infrastructure\Client\Model\UR_Client;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    protected static ?string $title = 'Создать клиента';

    public function form(Schema $schema): Schema
    {
        return AdminClientCreateForm::configure($schema);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        try {
            $result = app(CreateAdminClientUseCase::class)->execute(
                FilamentClientFormMapper::toCreateDto($data),
            );
        } catch (ApiException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();

            throw new Halt;
        }

        return UR_Client::query()->findOrFail($result['client']['id']);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Клиент создан';
    }

    protected function getRedirectUrl(): string
    {
        return ClientResource::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
