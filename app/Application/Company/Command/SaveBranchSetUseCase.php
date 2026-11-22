<?php

namespace App\Application\Company\Command;

use App\Application\Company\DTO\SaveBranchSetDto;
use App\Application\Company\Presenter\BranchMenuStructurePresenter;
use App\Domain\Supporting\Catalog\Enum\ProductStatus;
use App\Domain\Supporting\Catalog\Repository\CatalogItemRepository;
use App\Domain\Supporting\Company\Repository\BranchRepository;
use App\Domain\Supporting\Company\Repository\BranchSetRepository;

/**
 * Создание / обновление набора филиала: цена, состав и теги.
 */
final class SaveBranchSetUseCase
{
    public function __construct(
        private readonly BranchRepository $branches,
        private readonly CatalogItemRepository $catalog,
        private readonly BranchSetRepository $sets,
        private readonly BranchMenuStructurePresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(SaveBranchSetDto $dto): array
    {
        if ($this->branches->findById($dto->branchId) === null) {
            throw new \InvalidArgumentException('Филиал не найден.');
        }

        $name = trim($dto->name);
        if ($name === '') {
            throw new \InvalidArgumentException('Нужно название набора.');
        }

        if (ProductStatus::tryFrom($dto->status) === null) {
            throw new \InvalidArgumentException('Некорректный статус набора.');
        }

        if ($dto->lines === []) {
            throw new \InvalidArgumentException('В наборе должна быть хотя бы одна позиция.');
        }
        foreach ($dto->lines as $line) {
            if ($this->catalog->findProductById($line['catalog_item_id']) === null) {
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
