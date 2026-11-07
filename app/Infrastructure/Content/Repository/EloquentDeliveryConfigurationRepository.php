<?php

namespace App\Infrastructure\Content\Repository;

use App\Domain\Content\Entity\DeliveryConfiguration;
use App\Domain\Content\Repository\DeliveryConfigurationRepository;
use App\Infrastructure\Content\Mapper\DeliveryConfigurationMapper;
use App\Infrastructure\Content\Model\DLV_Configuration;

final class EloquentDeliveryConfigurationRepository implements DeliveryConfigurationRepository
{
    public function __construct(
        private readonly DeliveryConfigurationMapper $mapper,
    ) {}

    public function findPublic(): ?DeliveryConfiguration
    {
        $row = DLV_Configuration::query()->find(self::SINGLETON_ID);

        return $row instanceof DLV_Configuration ? $this->mapper->toDomain($row) : null;
    }

    public function saveSettings(
        ?int $minOrderAmountKopecks,
        ?int $averageDeliveryTimeMinutes,
        ?string $city,
        ?string $street,
        ?string $house,
        ?string $comment,
        ?string $searchLine,
        array $zones,
    ): DeliveryConfiguration {
        $row = DLV_Configuration::query()->find(self::SINGLETON_ID);
        if (! $row instanceof DLV_Configuration) {
            throw new \InvalidArgumentException('Настройки доставки не найдены.');
        }

        $stored = is_array($row->delivery_zones) ? $row->delivery_zones : [];
        $patches = [];
        foreach ($zones as $zone) {
            $patches[$zone['id']] = $zone;
        }

        foreach ($stored as $index => $storedZone) {
            if (! is_array($storedZone)) {
                continue;
            }
            $id = trim((string) ($storedZone['id'] ?? ''));
            if ($id === '' || ! isset($patches[$id])) {
                continue;
            }
            $patch = $patches[$id];
            $stored[$index]['name'] = $patch['name'];
            $stored[$index]['delivery_fee_kopecks'] = $patch['fee_rubles'] * 100;
            $stored[$index]['is_remote'] = $patch['is_remote'];
            unset($patches[$id]);
        }

        if ($patches !== []) {
            throw new \InvalidArgumentException('В списке есть зона, которой нет на карте.');
        }

        $row->fill([
            'min_order_amount_kopecks' => $minOrderAmountKopecks,
            'average_delivery_time_minutes' => $averageDeliveryTimeMinutes,
            'kitchen_city' => $city,
            'kitchen_street' => $street,
            'kitchen_house' => $house,
            'kitchen_address_comment' => $comment,
            'kitchen_address' => $searchLine,
            'delivery_zones' => $stored,
        ]);
        $row->save();

        return $this->mapper->toDomain($row);
    }
}
