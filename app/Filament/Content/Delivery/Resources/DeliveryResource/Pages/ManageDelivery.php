<?php

namespace App\Filament\Content\Delivery\Resources\DeliveryResource\Pages;

use App\Domain\Content\Repository\DeliveryConfigurationRepository;
use App\Filament\Content\Delivery\Resources\DeliveryResource;
use App\Filament\Content\Delivery\Resources\DeliveryResource\Schemas\DeliveryForm;
use App\Infrastructure\Content\Model\DLV_Configuration;
use App\Shared\Geo\AddressGeocoder;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

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
                'delivery_zones' => [],
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
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $zones = $data['delivery_zones'] ?? null;
        if (! is_array($zones) || $zones === []) {
            $legacy = $data['delivery_zone_geojson'] ?? null;
            if (is_array($legacy) && isset($legacy['type'])) {
                $data['delivery_zones'] = [[
                    'id' => (string) Str::uuid(),
                    'name' => 'Основная',
                    'delivery_fee_kopecks' => max(0, (int) ($data['delivery_fee_kopecks'] ?? 0)),
                    'is_remote' => false,
                    'geometry' => $legacy,
                ]];
            } else {
                $data['delivery_zones'] = [];
            }
        }

        return $data;
    }

    /**
     * Явный sync из iframe-моста (без гонки с $wire.set + save).
     *
     * @param  mixed  $zones
     */
    public function syncDeliveryZones(mixed $zones): void
    {
        $normalized = $this->normalizeZones($zones);

        data_set($this->data, 'delivery_zones', $normalized);
    }

    /**
     * Пересчитать lat/lng кухни по полям адреса (blur / кнопка / перед save).
     */
    public function refreshKitchenCoordinates(): void
    {
        $coords = $this->geocodeKitchenFromData(is_array($this->data) ? $this->data : []);
        if ($coords === null) {
            return;
        }

        data_set($this->data, 'kitchen_latitude', $coords['latitude']);
        data_set($this->data, 'kitchen_longitude', $coords['longitude']);

        $this->js('window.__activeDeliveryZoneBridge && window.__activeDeliveryZoneBridge.pushKitchenFromWire && window.__activeDeliveryZoneBridge.pushKitchenFromWire()');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $zones = $this->normalizeZones($data['delivery_zones'] ?? null);
        $data['delivery_zones'] = $zones;
        $data['delivery_zone_geojson'] = $this->combinedGeoJson($zones);
        $data['delivery_fee_kopecks'] = $this->minFeeKopecks($zones);
        $data['outside_zone_delivery_fee_kopecks'] = null;

        $coords = $this->geocodeKitchenFromData($data);
        if ($coords !== null) {
            $data['kitchen_latitude'] = $coords['latitude'];
            $data['kitchen_longitude'] = $coords['longitude'];
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{latitude: float, longitude: float}|null
     */
    private function geocodeKitchenFromData(array $data): ?array
    {
        $street = trim((string) ($data['kitchen_street'] ?? ''));
        $house = trim((string) ($data['kitchen_house'] ?? ''));
        $city = trim((string) ($data['kitchen_city'] ?? ''));
        $searchLine = trim((string) ($data['kitchen_address'] ?? ''));

        /** @var AddressGeocoder $geocoder */
        $geocoder = app(AddressGeocoder::class);

        if ($street !== '' && $house !== '') {
            return $geocoder->geocode($street, $house, $city !== '' ? $city : null);
        }

        if ($searchLine !== '') {
            return $geocoder->geocodeQuery($searchLine);
        }

        return null;
    }

    /**
     * @return list<array{
     *     id: string,
     *     name: string,
     *     delivery_fee_kopecks: int,
     *     is_remote: bool,
     *     geometry: array<string, mixed>
     * }>
     */
    private function normalizeZones(mixed $rawZones): array
    {
        if (! is_array($rawZones)) {
            return [];
        }

        $zones = [];

        foreach ($rawZones as $raw) {
            if (! is_array($raw)) {
                continue;
            }

            $geometry = $raw['geometry'] ?? null;
            if (! is_array($geometry)) {
                continue;
            }

            $type = $geometry['type'] ?? null;
            if (! is_string($type) || ! in_array($type, ['Polygon', 'MultiPolygon'], true)) {
                continue;
            }

            $coordinates = $geometry['coordinates'] ?? null;
            if (! is_array($coordinates) || $coordinates === []) {
                continue;
            }

            $id = trim((string) ($raw['id'] ?? ''));
            if ($id === '') {
                $id = (string) Str::uuid();
            }

            $name = trim((string) ($raw['name'] ?? ''));
            if ($name === '') {
                $name = 'Зона';
            }

            $zones[] = [
                'id' => $id,
                'name' => $name,
                'delivery_fee_kopecks' => max(0, (int) ($raw['delivery_fee_kopecks'] ?? 0)),
                'is_remote' => (bool) ($raw['is_remote'] ?? false),
                'geometry' => $geometry,
            ];
        }

        return $zones;
    }

    /**
     * @param  list<array{geometry: array<string, mixed>, delivery_fee_kopecks: int}>  $zones
     * @return array<string, mixed>|null
     */
    private function combinedGeoJson(array $zones): ?array
    {
        $polygons = [];

        foreach ($zones as $zone) {
            $geometry = $zone['geometry'];
            $type = $geometry['type'] ?? null;

            if ($type === 'Polygon') {
                $polygons[] = $geometry['coordinates'] ?? [];
            } elseif ($type === 'MultiPolygon') {
                foreach ($geometry['coordinates'] ?? [] as $polygonCoordinates) {
                    $polygons[] = $polygonCoordinates;
                }
            }
        }

        if ($polygons === []) {
            return null;
        }

        if (count($polygons) === 1) {
            return [
                'type' => 'Polygon',
                'coordinates' => $polygons[0],
            ];
        }

        return [
            'type' => 'MultiPolygon',
            'coordinates' => $polygons,
        ];
    }

    /**
     * @param  list<array{delivery_fee_kopecks: int}>  $zones
     */
    private function minFeeKopecks(array $zones): ?int
    {
        if ($zones === []) {
            return null;
        }

        return min(array_map(
            static fn (array $zone): int => (int) $zone['delivery_fee_kopecks'],
            $zones,
        ));
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
