<?php

namespace App\Application\Company\Command;

use App\Application\Company\DTO\SaveKitchenDto;
use App\Application\Company\Presenter\CompanyPresenter;
use App\Domain\Supporting\Company\Entity\Kitchen;
use App\Domain\Supporting\Company\Repository\KitchenRepository;

final class SaveKitchenUseCase
{
    public function __construct(
        private readonly KitchenRepository $kitchens,
        private readonly CompanyPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(SaveKitchenDto $dto): array
    {
        if ($dto->branchId < 1) {
            throw new \InvalidArgumentException('Нужен branch_id кухни.');
        }

        $city = $this->nullable($dto->city);
        $street = $this->nullable($dto->street);
        $house = $this->nullable($dto->house);

        if ($city === null && $street === null && $house === null) {
            throw new \InvalidArgumentException('Нужен хотя бы город, улица или дом кухни.');
        }

        if (
            ($dto->latitude === null) !== ($dto->longitude === null)
        ) {
            throw new \InvalidArgumentException('Широта и долгота задаются только парой.');
        }

        $kitchen = $this->kitchens->save(new Kitchen(
            id: $dto->id ?? 0,
            branchId: $dto->branchId,
            city: $city,
            street: $street,
            house: $house,
            comment: $this->nullable($dto->comment),
            latitude: $dto->latitude,
            longitude: $dto->longitude,
        ));

        return $this->presenter->kitchen($kitchen);
    }

    private function nullable(?string $value): ?string
    {
        $trimmed = trim((string) ($value ?? ''));

        return $trimmed !== '' ? $trimmed : null;
    }
}
