<?php

namespace App\Application\Operations\Delivery\Command;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Delivery\DTO\UpdateDeliveryZoneDTO;
use App\Application\Operations\Delivery\Query\GetAdminDeliveryZoneQuery;
use App\Domain\SystemContent\Repository\CompanyRepository;
use App\Domain\SystemContent\ValueObject\DeliveryZoneGeometry;
use InvalidArgumentException;

final class UpdateDeliveryZoneUseCase
{
    public function __construct(
        private readonly CompanyRepository $companies,
        private readonly GetAdminDeliveryZoneQuery $deliveryZoneQuery,
    ) {
    }

    public function execute(UpdateDeliveryZoneDTO $dto): array
    {
        $company = $this->companies->first();
        if ($company === null) {
            throw new ApiException('Company not found.', 404);
        }

        try {
            $geometry = DeliveryZoneGeometry::fromMixed($dto->deliveryZoneGeojson);
        } catch (InvalidArgumentException $exception) {
            throw new ApiException($exception->getMessage(), 422);
        }

        $updated = $company->withDeliveryZone(
            $geometry?->toArray(),
            $dto->kitchenLatitude,
            $dto->kitchenLongitude,
        );

        $this->companies->save($updated);

        return $this->deliveryZoneQuery->execute();
    }
}
