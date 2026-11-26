<?php

namespace App\Application\Menu\Command;

use App\Application\Menu\DTO\SaveBranchTagDto;
use App\Application\Menu\Presenter\BranchMenuStructurePresenter;
use App\Domain\Supporting\Menu\Port\MenuBranchLookupPort;
use App\Domain\Supporting\Menu\Repository\BranchTagRepository;

/**
 * Создание / обновление тега филиала.
 */
final class SaveBranchTagUseCase
{
    public function __construct(
        private readonly MenuBranchLookupPort $branches,
        private readonly BranchTagRepository $tags,
        private readonly BranchMenuStructurePresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(SaveBranchTagDto $dto): array
    {
        $this->branches->assertBranchExists($dto->branchId);

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
