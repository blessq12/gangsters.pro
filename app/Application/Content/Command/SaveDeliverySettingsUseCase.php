<?php

namespace App\Application\Content\Command;

use App\Application\Content\Query\GetDeliverySettingsUseCase;
use App\Domain\Content\Repository\DeliveryConfigurationRepository;

final class SaveDeliverySettingsUseCase
{
    public function __construct(
        private readonly DeliveryConfigurationRepository $delivery,
        private readonly GetDeliverySettingsUseCase $read,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function execute(array $payload): array
    {
        $zones = [];
        foreach (is_array($payload['zones'] ?? null) ? $payload['zones'] : [] as $zone) {
            if (! is_array($zone)) {
                continue;
            }
            $id = trim((string) ($zone['id'] ?? ''));
            $name = trim((string) ($zone['name'] ?? ''));
            if ($id === '' || $name === '') {
                throw new \InvalidArgumentException('У зоны нужны идентификатор и название.');
            }
            $zones[] = [
                'id' => $id,
                'name' => $name,
                'fee_rubles' => max(0, (int) ($zone['fee_rubles'] ?? 0)),
                'is_remote' => (bool) ($zone['is_remote'] ?? false),
            ];
        }

        $kitchen = is_array($payload['kitchen'] ?? null) ? $payload['kitchen'] : [];
        $minutes = $payload['average_delivery_time_minutes'] ?? null;
        $minOrder = $payload['min_order_rubles'] ?? null;

        $this->delivery->saveSettings(
            minOrderAmountKopecks: $minOrder === null || $minOrder === '' ? null : max(0, (int) $minOrder) * 100,
            averageDeliveryTimeMinutes: $minutes === null || $minutes === '' ? null : max(0, (int) $minutes),
            city: $this->nullable($kitchen['city'] ?? null),
            street: $this->nullable($kitchen['street'] ?? null),
            house: $this->nullable($kitchen['house'] ?? null),
            comment: $this->nullable($kitchen['comment'] ?? null),
            searchLine: $this->nullable($kitchen['search_line'] ?? null),
            zones: $zones,
        );

        return $this->read->execute();
    }

    private function nullable(mixed $value): ?string
    {
        $trimmed = trim((string) ($value ?? ''));

        return $trimmed !== '' ? $trimmed : null;
    }
}
