<?php

namespace App\Application\Company\Profile\Command;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Company\Profile\DTO\UpdateCompanyProfileDto;
use App\Application\Company\Profile\Query\GetAdminCompanyProfileQuery;
use App\Application\Company\Support\WorkScheduleNormalizer;
use App\Domain\SystemContent\Repository\CompanyRepository;

final class UpdateAdminCompanyProfileUseCase
{
    public function __construct(
        private readonly CompanyRepository $companies,
        private readonly GetAdminCompanyProfileQuery $profileQuery,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(UpdateCompanyProfileDto $dto): array
    {
        $company = $this->companies->first();
        if ($company === null) {
            throw new ApiException('Company not found.', 404);
        }

        $updated = $company->withProfile(
            name: $dto->name,
            brandName: $dto->brandName,
            description: $dto->description,
            tagline: $dto->tagline,
            country: $dto->country,
            state: $dto->state,
            city: $dto->city,
            street: $dto->street,
            house: $dto->house,
            addressComment: $dto->addressComment,
            cityCoverage: $dto->cityCoverage,
            phone: $dto->phone,
            phoneAdditional: $dto->phoneAdditional,
            supportPhone: $dto->supportPhone,
            whatsappPhone: $dto->whatsappPhone,
            emailAddress: $dto->emailAddress,
            publicEmail: $dto->publicEmail,
            workHours: $dto->workHours,
            deliveryHours: $dto->deliveryHours,
            workSchedule: WorkScheduleNormalizer::normalizeForStorage($dto->workSchedule),
            minOrderAmountKopecks: $dto->minOrderAmountKopecks,
            deliveryFeeKopecks: $dto->deliveryFeeKopecks,
            averageDeliveryTimeMinutes: $dto->averageDeliveryTimeMinutes,
            telegram: $dto->telegram,
            siteUrl: $dto->siteUrl,
            vk: $dto->vk,
            inst: $dto->inst,
        );

        $this->companies->save($updated);

        return $this->profileQuery->execute();
    }
}
