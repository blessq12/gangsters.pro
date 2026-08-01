<?php

namespace App\Application\YandexFood\useCases;

use App\Application\Content\useCases\GetDeliveryDataUseCase;
use App\Domain\Content\Repository\CompanyRepository;

final class GetYandexFoodRestaurantsUseCase
{
    public function __construct(
        private readonly CompanyRepository $company,
        private readonly GetDeliveryDataUseCase $deliveryData,
    ) {}

    /**
     * @return array{places: list<array{id: string, title: string, address: string}>}
     */
    public function execute(): array
    {
        $company = $this->company->findPublic();
        $delivery = $this->deliveryData->executeLite();
        $kitchenAddress = $delivery['data']['zone']['kitchen_address'] ?? null;

        $title = $company?->name() ?? 'Ресторан';
        $address = $this->formatKitchenAddress(is_array($kitchenAddress) ? $kitchenAddress : null);

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

    /**
     * @param  array<string, string|null>|null  $kitchenAddress
     */
    private function formatKitchenAddress(?array $kitchenAddress): string
    {
        if ($kitchenAddress === null) {
            return '';
        }

        $street = $kitchenAddress['street'] ?? null;
        $parts = array_filter([
            $kitchenAddress['city'] ?? null,
            is_string($street) ? $this->formatStreetLine($street) : null,
            $kitchenAddress['house'] ?? null,
        ], static fn (mixed $part): bool => is_string($part) && trim($part) !== '');

        if ($parts !== []) {
            return implode(', ', $parts);
        }

        $searchLine = $kitchenAddress['search_line'] ?? null;

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
