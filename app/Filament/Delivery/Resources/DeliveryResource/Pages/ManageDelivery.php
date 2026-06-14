<?php

namespace App\Filament\Delivery\Resources\DeliveryResource\Pages;

use App\Domain\Delivery\Repository\DeliveryConfigurationRepository;
use App\Filament\Delivery\Resources\DeliveryResource;
use App\Filament\Delivery\Resources\DeliveryResource\Schemas\DeliveryForm;
use App\Infrastructure\Delivery\Model\DLV_Configuration;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class ManageDelivery extends EditRecord
{
    protected static string $resource = DeliveryResource::class;

    protected static ?string $title = 'Доставка';

    protected static ?string $navigationLabel = 'Доставка';

    public function mount(int|string $record = DeliveryConfigurationRepository::SINGLETON_ID): void
    {
        DLV_Configuration::query()->firstOrCreate(
            ['id' => DeliveryConfigurationRepository::SINGLETON_ID],
            [
                'min_order_amount_kopecks' => null,
                'delivery_fee_kopecks' => null,
                'outside_zone_delivery_fee_kopecks' => null,
                'average_delivery_time_minutes' => null,
            ],
        );

        parent::mount($record);
    }

    public function form(Schema $schema): Schema
    {
        return DeliveryForm::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Сохранить')
                ->submit('save'),
        ];
    }

    protected function getRedirectUrl(): ?string
    {
        return null;
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Настройки доставки сохранены');
    }
}
