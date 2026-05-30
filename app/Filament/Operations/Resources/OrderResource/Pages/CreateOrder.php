<?php

namespace App\Filament\Operations\Resources\OrderResource\Pages;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Order\Command\CreateAdminOrderUseCase;
use App\Filament\Operations\Resources\OrderResource;
use App\Filament\Operations\Resources\OrderResource\Schemas\AdminOrderCreateForm;
use App\Filament\Operations\Support\FilamentOrderFormMapper;
use App\Infrastructure\Order\Model\ORD_Order;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected static ?string $title = 'Создать заказ';

    public function form(Schema $schema): Schema
    {
        return AdminOrderCreateForm::configure($schema);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        try {
            $detail = app(CreateAdminOrderUseCase::class)->execute(
                FilamentOrderFormMapper::toCreateAdminOrderDto($data),
            );
        } catch (ApiException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();

            throw new Halt;
        }

        return ORD_Order::query()->findOrFail($detail['id']);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Заказ создан';
    }

    protected function getRedirectUrl(): string
    {
        return OrderResource::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
