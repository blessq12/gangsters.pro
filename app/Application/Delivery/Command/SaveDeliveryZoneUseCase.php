<?php

namespace App\Application\Delivery\Command;

use App\Application\Delivery\DTO\SaveDeliveryZoneDto;
use App\Application\Delivery\Presenter\DeliveryPresenter;
use App\Domain\Delivery\Repository\DeliveryConfigurationRepository;
use App\Domain\Delivery\ValueObject\DeliveryZone;
use Illuminate\Support\Str;

final class SaveDeliveryZoneUseCase
{
    public function __construct(
        private readonly DeliveryConfigurationRepository $delivery,
        private readonly DeliveryPresenter $presenter,
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

        $current = $this->delivery->findPublic();
        if ($current === null) {
            throw new \InvalidArgumentException('Настройки доставки не найдены.');
        }

        if (! $isCreate) {
            $exists = false;
            foreach ($current->zones() as $zone) {
                if ($zone->id() === $id) {
                    $exists = true;
                    break;
                }
            }
            if (! $exists) {
                throw new \InvalidArgumentException('Зона не найдена.');
            }
        }

        $kitchen = $current->kitchenAddress();
        $configuration = $this->delivery->saveSettings(
            minOrderAmountKopecks: $current->minOrderAmountKopecks(),
            averageDeliveryTimeMinutes: $current->averageDeliveryTimeMinutes(),
            city: $kitchen->city(),
            street: $kitchen->street(),
            house: $kitchen->house(),
            comment: $kitchen->comment(),
            searchLine: $kitchen->searchLine(),
            zones: [[
                'id' => $id,
                'name' => $name,
                'fee_rubles' => $dto->feeRubles,
                'is_remote' => $dto->isRemote,
                'geometry' => $dto->geometry,
            ]],
        );

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
