<?php

namespace App\Application\Branch\Command;

use App\Application\Branch\Presenter\DeliveryPresenter;
use App\Domain\Supporting\Branch\Repository\BranchRepository;
use App\Domain\Supporting\Branch\Repository\DeliveryConfigurationRepository;
use Illuminate\Support\Str;

final class SaveDeliveryZonesUseCase
{
    public function __construct(
        private readonly DeliveryConfigurationRepository $delivery,
        private readonly BranchRepository $branches,
        private readonly DeliveryPresenter $presenter,
    ) {}

    /**
     * Полная замена набора зон филиала (с геометрией).
     *
     * @param  list<array<string, mixed>>  $zones
     * @return array<string, mixed>
     */
    public function execute(int $branchId, array $zones): array
    {
        if ($branchId < 1) {
            throw new \InvalidArgumentException('Некорректный branch_id.');
        }

        if ($this->branches->findById($branchId) === null) {
            throw new \InvalidArgumentException('Филиал не найден.');
        }

        $current = $this->delivery->findByBranch($branchId);
        if ($current === null) {
            throw new \InvalidArgumentException('Настройки доставки филиала не найдены.');
        }

        $normalized = [];
        foreach ($zones as $index => $zone) {
            if (! is_array($zone)) {
                continue;
            }

            $id = trim((string) ($zone['id'] ?? ''));
            if ($id === '') {
                $id = (string) Str::uuid();
            }

            $name = trim((string) ($zone['name'] ?? ''));
            if ($name === '') {
                $name = 'Зона '.($index + 1);
            }

            $fee = (int) ($zone['fee'] ?? $zone['fee_rubles'] ?? 0);
            if ($fee < 0) {
                throw new \InvalidArgumentException('Тариф зоны не может быть отрицательным.');
            }

            $geometry = $zone['geometry'] ?? null;
            if (! is_array($geometry)) {
                throw new \InvalidArgumentException('У каждой зоны нужен geometry (контур на карте).');
            }
            $this->assertValidGeometry($geometry);

            $normalized[] = [
                'id' => $id,
                'name' => $name,
                'fee_rubles' => $fee,
                'is_remote' => (bool) ($zone['is_remote'] ?? false),
                'geometry' => $geometry,
            ];
        }

        $configuration = $this->delivery->saveSettingsForBranch(
            branchId: $branchId,
            minOrderAmountKopecks: $current->minOrderAmountKopecks(),
            averageDeliveryTimeMinutes: $current->averageDeliveryTimeMinutes(),
            zones: $normalized,
        );

        return $this->presenter->present(
            $configuration,
            $this->presenter->addressFromBranch($this->branches->findById($branchId)),
        );
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
