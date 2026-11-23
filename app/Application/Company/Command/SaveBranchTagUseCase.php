<?php

namespace App\Application\Company\Command;

use App\Application\Company\DTO\SaveBranchTagDto;
use App\Application\Company\Presenter\BranchMenuStructurePresenter;
use App\Domain\Supporting\Company\Repository\BranchRepository;
use App\Domain\Supporting\Company\Repository\BranchTagRepository;

/**
 * Создание / обновление тега филиала.
 */
final class SaveBranchTagUseCase
{
    public function __construct(
        private readonly BranchRepository $branches,
        private readonly BranchTagRepository $tags,
        private readonly BranchMenuStructurePresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(SaveBranchTagDto $dto): array
    {
        if ($this->branches->findById($dto->branchId) === null) {
            throw new \InvalidArgumentException('Филиал не найден.');
        }

        $code = trim($dto->code);
        $label = trim($dto->label);
        if ($code === '') {
            throw new \InvalidArgumentException('Нужен код тега.');
        }
        if ($label === '') {
            throw new \InvalidArgumentException('Нужна подпись тега.');
        }

        $saved = $this->tags->save(
            $dto->branchId,
            $dto->id,
            $code,
            $label,
            $dto->color,
            $dto->isActive,
            $dto->sortOrder,
        );

        return $this->presenter->tag($saved);
    }
}
