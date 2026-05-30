<?php

namespace Tests\Unit\Application\Operations\Delivery;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Delivery\Command\UpdateDeliveryZoneUseCase;
use App\Application\Operations\Delivery\DTO\UpdateDeliveryZoneDto;
use App\Application\Operations\Delivery\Query\GetAdminDeliveryZoneQuery;
use App\Domain\SystemContent\Entity\Company;
use App\Domain\SystemContent\Repository\CompanyRepository;
use PHPUnit\Framework\TestCase;

final class UpdateDeliveryZoneUseCaseTest extends TestCase
{
    public function test_execute_rejects_invalid_geojson(): void
    {
        $company = new Company(
            id: 1,
            name: 'Gangsters',
            brandName: null,
            description: null,
            tagline: null,
            country: null,
            state: null,
            city: null,
            street: null,
            house: null,
            addressComment: null,
            cityCoverage: null,
            deliveryZoneGeojson: null,
            kitchenLatitude: null,
            kitchenLongitude: null,
            phone: null,
            phoneAdditional: null,
            supportPhone: null,
            whatsappPhone: null,
            emailAddress: null,
            publicEmail: null,
            workHours: null,
            deliveryHours: null,
            workSchedule: null,
            minOrderAmountKopecks: null,
            deliveryFeeKopecks: null,
            averageDeliveryTimeMinutes: null,
            telegram: null,
            siteUrl: null,
            vk: null,
            inst: null,
        );

        $repo = new class($company) implements CompanyRepository {
            public function __construct(private Company $company) {}

            public function first(): ?Company
            {
                return $this->company;
            }

            public function save(Company $company): void
            {
                $this->company = $company;
            }
        };

        $useCase = new UpdateDeliveryZoneUseCase(
            $repo,
            new GetAdminDeliveryZoneQuery($repo),
        );

        $this->expectException(ApiException::class);

        $useCase->execute(new UpdateDeliveryZoneDto(
            deliveryZoneGeojson: ['type' => 'Point', 'coordinates' => [0, 0]],
            kitchenLatitude: null,
            kitchenLongitude: null,
        ));
    }
}
