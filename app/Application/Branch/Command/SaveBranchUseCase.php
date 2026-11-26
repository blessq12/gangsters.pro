<?php

namespace App\Application\Branch\Command;

use App\Application\Branch\DTO\SaveBranchDto;
use App\Application\Branch\Presenter\BranchPresenter;
use App\Domain\Supporting\Branch\Entity\Branch;
use App\Domain\Supporting\Branch\Repository\BranchRepository;
use App\Domain\Supporting\Branch\Support\ScheduleNormalizer;
use App\Shared\ValueObject\PhoneNumber;

final class SaveBranchUseCase
{
    public function __construct(
        private readonly BranchRepository $branches,
        private readonly BranchPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(SaveBranchDto $dto): array
    {
        $name = trim($dto->name);
        if ($name === '') {
            throw new \InvalidArgumentException('Нужно название филиала.');
        }

        if ($dto->companyId < 1) {
            throw new \InvalidArgumentException('Нужен company_id филиала.');
        }

        if (($dto->latitude === null) !== ($dto->longitude === null)) {
            throw new \InvalidArgumentException('Широта и долгота задаются только парой.');
        }

        $phone = $this->nullable($dto->phone);
        if ($phone !== null && PhoneNumber::tryFromRaw($phone) === null) {
            throw new \InvalidArgumentException('Некорректный телефон филиала.');
        }

        $branch = $this->branches->save(new Branch(
            id: $dto->id ?? 0,
            companyId: $dto->companyId,
            name: $name,
            isActive: $dto->isActive,
            schedule: ScheduleNormalizer::normalize($dto->schedule),
            city: $this->nullable($dto->city),
            street: $this->nullable($dto->street),
            house: $this->nullable($dto->house),
            comment: $this->nullable($dto->comment),
            latitude: $dto->latitude,
            longitude: $dto->longitude,
            phone: $phone,
        ));

        return $this->presenter->branch($branch);
    }

    private function nullable(?string $value): ?string
    {
        $trimmed = trim((string) ($value ?? ''));

        return $trimmed !== '' ? $trimmed : null;
    }
}
