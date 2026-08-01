<?php

namespace App\Application\YandexFood\useCases;

use App\Domain\Content\Entity\DeliveryConfiguration;
use App\Domain\Content\Repository\CompanyRepository;
use App\Domain\Content\Repository\DeliveryConfigurationRepository;
use App\Domain\Content\ValueObject\KitchenAddress;

final class GetYandexFoodRestaurantsUseCase
{
    public function __construct(
        private readonly CompanyRepository $company,
        private readonly DeliveryConfigurationRepository $delivery,
    ) {}

    /**
     * @return array{places: list<array{id: string, title: string, address: string}>}
     */
    public function execute(): array
    {
        $company = $this->company->findPublic();
        $config = $this->delivery->findPublic();
        $kitchenAddress = $config instanceof DeliveryConfiguration
            ? $config->kitchenAddress()
            : null;

        $title = $company?->name() ?? 'Ресторан';
        $address = $this->formatKitchenAddress($kitchenAddress);

        $restaurantId = config('yandex_food.restaurant_id', '1');

        return [
            'places' => [
                [
                    'id' => is_string($restaurantId) && $restaurantId !== '' ? $restaurantId : '1',
                    'title' => $title,
                    'address' => $address,
                ],
            ],
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
