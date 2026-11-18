<?php

namespace App\Application\Company\Command;

use App\Application\Company\DTO\SaveBranchDto;
use App\Application\Company\Presenter\CompanyPresenter;
use App\Domain\Company\Entity\Branch;
use App\Domain\Company\Repository\BranchRepository;

final class SaveBranchUseCase
{
    public function __construct(
        private readonly BranchRepository $branches,
        private readonly CompanyPresenter $presenter,
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

        $branch = $this->branches->save(new Branch(
            id: $dto->id ?? 0,
            companyId: $dto->companyId,
            name: $name,
            isActive: $dto->isActive,
        ));

        return $this->presenter->branch($branch);
    }
}
