<?php

namespace App\Application\Delivery\Command;

use App\Application\Delivery\DTO\SaveDeliveryZoneDto;
use App\Application\Delivery\Event\DeliveryZonesChangedFactory;
use App\Application\Delivery\Presenter\DeliveryPresenter;
use App\Domain\Core\Delivery\Tariff\Repository\DeliveryConfigurationRepository;
use App\Domain\Core\Delivery\Tariff\ValueObject\DeliveryZone;
use Illuminate\Support\Str;

final class SaveDeliveryZoneUseCase
{
    public function __construct(
        private readonly DeliveryConfigurationRepository $delivery,
        private readonly DeliveryPresenter $presenter,
        private readonly DeliveryZonesChangedFactory $zonesChanged,
    ) {}

    /**
     * @return array{zone: array<string, mixed>, created: bool}
     */
    public function execute(SaveDeliveryZoneDto $dto): array
    {
        $name = trim($dto->name);
        if ($name === '') {
            throw new \InvalidArgumentException('Нужно название зоны.');
        }

        if ($dto->feeRubles < 0) {
            throw new \InvalidArgumentException('Тариф зоны не может быть отрицательным.');
        }

        $incomingId = $dto->id !== null ? trim($dto->id) : '';
        $isCreate = $incomingId === '';
        $id = $isCreate ? (string) Str::uuid() : $incomingId;

        if ($isCreate && $dto->geometry === null) {
            throw new \InvalidArgumentException('Для новой зоны нужен geometry (GeoJSON).');
        }

        if ($dto->geometry !== null) {
            $this->assertValidGeometry($dto->geometry);
        }

        if ($dto->branchId < 1) {
            throw new \InvalidArgumentException('Некорректный branch_id.');
        }

        $current = $this->delivery->findByBranch($dto->branchId);
        if ($current === null) {
            throw new \InvalidArgumentException('Настройки доставки филиала не найдены.');
        }

        $zones = [];
        $exists = false;
        foreach ($current->zones() as $zone) {
            if ($zone->id() === $id) {
                $exists = true;
                $zones[] = [
                    'id' => $id,
                    'name' => $name,
                    'fee_rubles' => $dto->feeRubles,
                    'is_remote' => $dto->isRemote,
                    'geometry' => $dto->geometry ?? $zone->geometry(),
                ];

                continue;
            }

            $zones[] = [
                'id' => $zone->id(),
                'name' => $zone->name(),
                'fee_rubles' => intdiv($zone->deliveryFeeKopecks(), 100),
                'is_remote' => $zone->isRemote(),
                'geometry' => $zone->geometry(),
            ];
        }

        if (! $isCreate && ! $exists) {
            throw new \InvalidArgumentException('Зона не найдена.');
        }

        if ($isCreate) {
            $zones[] = [
                'id' => $id,
                'name' => $name,
                'fee_rubles' => $dto->feeRubles,
                'is_remote' => $dto->isRemote,
                'geometry' => $dto->geometry,
            ];
        }

        $configuration = $this->delivery->saveSettingsForBranch(
            branchId: $dto->branchId,
            minOrderAmountKopecks: $current->minOrderAmountKopecks(),
            averageDeliveryTimeMinutes: $current->averageDeliveryTimeMinutes(),
            zones: $zones,
        );

        $event = $this->zonesChanged->createIfChanged($this->zonesChanged->snapshot($current), $configuration);
        if ($event !== null) {
            event($event);
        }

        $matched = null;
        foreach ($configuration->zones() as $zone) {
            if ($zone->id() === $id) {
                $matched = $zone;
                break;
            }
        }

        if (! $matched instanceof DeliveryZone) {
            throw new \RuntimeException('Зона не сохранилась.');
        }

        return [
            'zone' => $this->presenter->zone($matched),
            'created' => $isCreate,
        ];
    }

    /**
     * @param  array<string, mixed>  $geometry
     */
    private function assertValidGeometry(array $geometry): void
    {
        $type = $geometry['type'] ?? null;
        if ($type !== 'Polygon' && $type !== 'MultiPolygon') {
            throw new \InvalidArgumentException('geometry.type должен быть Polygon или MultiPolygon.');
        }

        if (! isset($geometry['coordinates']) || ! is_array($geometry['coordinates']) || $geometry['coordinates'] === []) {
            throw new \InvalidArgumentException('geometry.coordinates обязателен.');
        }
    }
}
