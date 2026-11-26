<?php

namespace App\Application\Menu\Command;

use App\Application\Menu\DTO\SaveBranchSetDto;
use App\Application\Menu\Presenter\BranchMenuStructurePresenter;
use App\Domain\Supporting\Menu\Port\MenuBranchLookupPort;
use App\Domain\Supporting\Menu\Port\MenuCatalogLookupPort;
use App\Domain\Supporting\Menu\Repository\BranchSetRepository;

/**
 * Создание / обновление набора филиала: цена, состав и теги.
 */
final class SaveBranchSetUseCase
{
    private const ALLOWED_STATUSES = ['active', 'archived'];

    public function __construct(
        private readonly MenuBranchLookupPort $branches,
        private readonly MenuCatalogLookupPort $catalog,
        private readonly BranchSetRepository $sets,
        private readonly BranchMenuStructurePresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(SaveBranchSetDto $dto): array
    {
        $this->branches->assertBranchExists($dto->branchId);

        $name = trim($dto->name);
        if ($name === '') {
            throw new \InvalidArgumentException('Нужно название набора.');
        }

        if (! in_array($dto->status, self::ALLOWED_STATUSES, true)) {
            throw new \InvalidArgumentException('Некорректный статус набора.');
        }

        if ($dto->lines === []) {
            throw new \InvalidArgumentException('В наборе должна быть хотя бы одна позиция.');
        }
        foreach ($dto->lines as $line) {
            if (! $this->catalog->productExists($line['catalog_item_id'])) {
                throw new \InvalidArgumentException('Товар каталога не найден: '.$line['catalog_item_id'].'.');
            }
        }

        $description = $dto->description !== null ? trim($dto->description) : '';

        $saved = $this->sets->save(
            $dto->branchId,
            $dto->id,
            $name,
            (string) $dto->slug,
            $description !== '' ? $description : null,
            $dto->isAvailable,
            $dto->priceKopecks,
            $dto->status,
            $dto->lines,
            $dto->tagIds,
        );

        return $this->presenter->set($saved);
    }
}
