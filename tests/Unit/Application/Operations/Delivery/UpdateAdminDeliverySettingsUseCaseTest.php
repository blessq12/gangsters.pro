<?php

namespace Tests\Unit\Application\Operations\Delivery;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Delivery\Command\UpdateAdminDeliverySettingsUseCase;
use App\Application\Operations\Delivery\DTO\UpdateDeliverySettingsDto;
use App\Application\Operations\Delivery\Query\GetAdminDeliverySettingsQuery;
use App\Domain\SystemContent\Entity\Company;
use App\Domain\SystemContent\Repository\CompanyRepository;
use PHPUnit\Framework\TestCase;

final class UpdateAdminDeliverySettingsUseCaseTest extends TestCase
{
    public function test_execute_rejects_invalid_geojson(): void
    {
        $company = $this->sampleCompany();

        $repo = $this->repositoryFor($company);

        $useCase = new UpdateAdminDeliverySettingsUseCase(
            $repo,
            new GetAdminDeliverySettingsQuery($repo),
        );

        $this->expectException(ApiException::class);

        $useCase->execute(new UpdateDeliverySettingsDto(
            deliveryZoneGeojson: ['type' => 'Point', 'coordinates' => [0, 0]],
            kitchenLatitude: null,
            kitchenLongitude: null,
            deliveryHours: null,
            minOrderAmountKopecks: null,
            deliveryFeeKopecks: null,
            averageDeliveryTimeMinutes: null,
        ));
    }

    public function test_execute_persists_delivery_settings(): void
    {
        $company = $this->sampleCompany();
        $repo = $this->repositoryFor($company);

        $useCase = new UpdateAdminDeliverySettingsUseCase(
            $repo,
            new GetAdminDeliverySettingsQuery($repo),
        );

        $useCase->execute(new UpdateDeliverySettingsDto(
            deliveryZoneGeojson: null,
            kitchenLatitude: 55.75,
            kitchenLongitude: 37.62,
            deliveryHours: '10:00–23:00',
            minOrderAmountKopecks: 1500_00,
            deliveryFeeKopecks: 200_00,
            averageDeliveryTimeMinutes: 45,
        ));

        $saved = $repo->first();
        $this->assertNotNull($saved);
        $this->assertSame('10:00–23:00', $saved->deliveryHours());
        $this->assertSame(1500_00, $saved->minOrderAmountKopecks());
        $this->assertSame(200_00, $saved->deliveryFeeKopecks());
        $this->assertSame(45, $saved->averageDeliveryTimeMinutes());
        $this->assertSame(55.75, $saved->kitchenLatitude());
    }

    public function test_execute_rejects_negative_delivery_fee(): void
    {
        $repo = $this->repositoryFor($this->sampleCompany());

        $useCase = new UpdateAdminDeliverySettingsUseCase(
            $repo,
            new GetAdminDeliverySettingsQuery($repo),
        );

        $this->expectException(ApiException::class);

        $useCase->execute(new UpdateDeliverySettingsDto(
            deliveryZoneGeojson: null,
            kitchenLatitude: null,
            kitchenLongitude: null,
            deliveryHours: null,
            minOrderAmountKopecks: null,
            deliveryFeeKopecks: -1,
            averageDeliveryTimeMinutes: null,
        ));
    }

    private function sampleCompany(): Company
    {
        return new Company(
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
            logo: null,
        );
    }

    /**
     * @return CompanyRepository&object{first(): ?Company}
     */
    private function repositoryFor(Company $company): CompanyRepository
    {
        return new class($company) implements CompanyRepository
        {
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
    }
}
