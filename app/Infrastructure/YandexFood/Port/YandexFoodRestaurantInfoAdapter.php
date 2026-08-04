<?php

namespace App\Infrastructure\YandexFood\Port;

use App\Application\YandexFood\Port\YandexFoodRestaurantInfoPort;
use App\Domain\Content\Entity\DeliveryConfiguration;
use App\Domain\Content\Repository\CompanyRepository;
use App\Domain\Content\Repository\DeliveryConfigurationRepository;
use App\Domain\Content\ValueObject\KitchenAddress;

final class YandexFoodRestaurantInfoAdapter implements YandexFoodRestaurantInfoPort
{
    public function __construct(
        private readonly CompanyRepository $company,
        private readonly DeliveryConfigurationRepository $delivery,
    ) {}

    public function readRestaurantInfo(): array
    {
        $company = $this->company->findPublic();
        $config = $this->delivery->findPublic();
        $kitchenAddress = $config instanceof DeliveryConfiguration
            ? $config->kitchenAddress()
            : null;

        return [
            'title' => $company?->name() ?? 'Ресторан',
            'address' => $this->formatKitchenAddress($kitchenAddress),
        ];
    }

    private function formatKitchenAddress(?KitchenAddress $kitchenAddress): string
    {
        if ($kitchenAddress === null) {
            return '';
        }

        $street = $kitchenAddress->street();
        $parts = array_filter([
            $kitchenAddress->city(),
            is_string($street) && $street !== '' ? $this->formatStreetLine($street) : null,
            $kitchenAddress->house(),
        ], static fn (mixed $part): bool => is_string($part) && trim($part) !== '');

        if ($parts !== []) {
            return implode(', ', $parts);
        }

        $searchLine = $kitchenAddress->searchLine();

        return is_string($searchLine) ? $searchLine : '';
    }

    private function formatStreetLine(string $street): string
    {
        $street = trim($street);
        if ($street === '') {
            return '';
        }

        if (preg_match('/^(ул\.|улица|пр\.|проспект|пер\.|переулок|б-р|бульвар|ш\.|шоссе)\s/iu', $street) === 1) {
            return $street;
        }

        return 'ул. '.$street;
    }
}
