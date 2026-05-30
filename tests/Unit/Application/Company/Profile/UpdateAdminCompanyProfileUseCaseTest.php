<?php

namespace Tests\Unit\Application\Company\Profile;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Company\Profile\Command\UpdateAdminCompanyProfileUseCase;
use App\Application\Company\Profile\DTO\UpdateCompanyProfileDto;
use App\Application\Company\Profile\Query\GetAdminCompanyProfileQuery;
use App\Domain\SystemContent\Entity\Company;
use App\Domain\SystemContent\Repository\CompanyRepository;
use PHPUnit\Framework\TestCase;

final class UpdateAdminCompanyProfileUseCaseTest extends TestCase
{
    public function test_execute_throws_when_company_missing(): void
    {
        $repo = new class implements CompanyRepository
        {
            public function first(): ?Company
            {
                return null;
            }

            public function save(Company $company): void {}
        };

        $useCase = new UpdateAdminCompanyProfileUseCase(
            $repo,
            new GetAdminCompanyProfileQuery($repo),
        );

        $this->expectException(ApiException::class);

        $useCase->execute(new UpdateCompanyProfileDto(
            name: 'Test',
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
            phone: null,
            phoneAdditional: null,
            supportPhone: null,
            whatsappPhone: null,
            emailAddress: null,
            publicEmail: null,
            workHours: null,
            workSchedule: null,
            telegram: null,
            siteUrl: null,
            vk: null,
            inst: null,
        ));
    }

    public function test_execute_does_not_change_delivery_settings(): void
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
            workHours: '9-18',
            deliveryHours: '10-22',
            workSchedule: null,
            minOrderAmountKopecks: 1000_00,
            deliveryFeeKopecks: 150_00,
            averageDeliveryTimeMinutes: 30,
            telegram: null,
            siteUrl: null,
            vk: null,
            inst: null,
            logo: null,
        );

        $repo = new class($company) implements CompanyRepository
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

        $useCase = new UpdateAdminCompanyProfileUseCase(
            $repo,
            new GetAdminCompanyProfileQuery($repo),
        );

        $useCase->execute(new UpdateCompanyProfileDto(
            name: 'Gangsters Updated',
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
            phone: null,
            phoneAdditional: null,
            supportPhone: null,
            whatsappPhone: null,
            emailAddress: null,
            publicEmail: null,
            workHours: '8-20',
            workSchedule: null,
            telegram: null,
            siteUrl: null,
            vk: null,
            inst: null,
        ));

        $saved = $repo->first();
        $this->assertNotNull($saved);
        $this->assertSame('Gangsters Updated', $saved->name());
        $this->assertSame('8-20', $saved->workHours());
        $this->assertSame('10-22', $saved->deliveryHours());
        $this->assertSame(1000_00, $saved->minOrderAmountKopecks());
        $this->assertSame(150_00, $saved->deliveryFeeKopecks());
        $this->assertSame(30, $saved->averageDeliveryTimeMinutes());
    }
}
