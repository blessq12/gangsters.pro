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
        $repo = new class implements CompanyRepository {
            public function first(): ?Company
            {
                return null;
            }

            public function save(Company $company): void
            {
            }
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
            deliveryHours: null,
            workSchedule: null,
            minOrderAmountKopecks: null,
            deliveryFeeKopecks: null,
            averageDeliveryTimeMinutes: null,
            telegram: null,
            siteUrl: null,
            vk: null,
            inst: null,
        ));
    }
}
