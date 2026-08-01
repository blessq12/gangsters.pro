<?php

namespace App\Filament\Content\Delivery\Resources\DeliveryResource\Pages;

use App\Domain\Content\Repository\DeliveryConfigurationRepository;
use App\Filament\Content\Delivery\Resources\DeliveryResource;
use App\Filament\Content\Delivery\Resources\DeliveryResource\Schemas\DeliveryForm;
use App\Infrastructure\Content\Model\DLV_Configuration;
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
                ->alpineClickHandler('window.deliveryZoneSyncBeforeSave($wire)')
                ->livewireClickHandlerEnabled(false),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $geoJson = $data['delivery_zone_geojson'] ?? null;

        if ($geoJson === null || $geoJson === '') {
            $data['delivery_zone_geojson'] = null;

            return $data;
        }

        if (! is_array($geoJson)) {
            $data['delivery_zone_geojson'] = null;

            return $data;
        }

        $type = $geoJson['type'] ?? null;
        if (! is_string($type) || ! in_array($type, ['Polygon', 'MultiPolygon'], true)) {
            $data['delivery_zone_geojson'] = null;
        }

        return $data;
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
