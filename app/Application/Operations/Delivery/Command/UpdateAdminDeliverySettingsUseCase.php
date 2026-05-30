<?php

namespace App\Application\Operations\Delivery\Command;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Delivery\DTO\UpdateDeliverySettingsDto;
use App\Application\Operations\Delivery\Query\GetAdminDeliverySettingsQuery;
use App\Application\Operations\Delivery\Support\DeliverySettingsValidator;
use App\Domain\SystemContent\Repository\CompanyRepository;
use App\Domain\SystemContent\ValueObject\DeliveryZoneGeometry;
use InvalidArgumentException;

final class UpdateAdminDeliverySettingsUseCase
{
    public function __construct(
        private readonly CompanyRepository $companies,
        private readonly GetAdminDeliverySettingsQuery $deliverySettingsQuery,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(UpdateDeliverySettingsDto $dto): array
    {
        $company = $this->companies->first();
        if ($company === null) {
            throw new ApiException('Company not found.', 404);
        }

        DeliverySettingsValidator::assertValid($dto);

        try {
            $geometry = DeliveryZoneGeometry::fromMixed($dto->deliveryZoneGeojson);
        } catch (InvalidArgumentException $exception) {
            throw new ApiException($exception->getMessage(), 422);
        }

        $updated = $company
            ->withDeliveryZone(
                $geometry?->toArray(),
                $dto->kitchenLatitude,
                $dto->kitchenLongitude,
            )
            ->withDeliverySettings(
                deliveryHours: $dto->deliveryHours,
                minOrderAmountKopecks: $dto->minOrderAmountKopecks,
                deliveryFeeKopecks: $dto->deliveryFeeKopecks,
                averageDeliveryTimeMinutes: $dto->averageDeliveryTimeMinutes,
            );

        $this->companies->save($updated);

        return $this->deliverySettingsQuery->execute();
    }
}
