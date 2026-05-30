<?php

namespace App\Application\Company\Profile\DTO;

final readonly class UpdateCompanyProfileDto
{
    /**
     * @param  array<int, array<string, mixed>>|null  $workSchedule
     */
    public function __construct(
        public ?string $name,
        public ?string $brandName,
        public ?string $description,
        public ?string $tagline,
        public ?string $country,
        public ?string $state,
        public ?string $city,
        public ?string $street,
        public ?string $house,
        public ?string $addressComment,
        public ?string $cityCoverage,
        public ?string $phone,
        public ?string $phoneAdditional,
        public ?string $supportPhone,
        public ?string $whatsappPhone,
        public ?string $emailAddress,
        public ?string $publicEmail,
        public ?string $workHours,
        public ?string $deliveryHours,
        public ?array $workSchedule,
        public ?int $minOrderAmountKopecks,
        public ?int $deliveryFeeKopecks,
        public ?int $averageDeliveryTimeMinutes,
        public ?string $telegram,
        public ?string $siteUrl,
        public ?string $vk,
        public ?string $inst,
    ) {
    }
}
