<?php

namespace App\Application\Menu\Service;

use App\Domain\Supporting\Menu\Enum\BranchMenuEntryKind;
use App\Domain\Supporting\Menu\Port\MenuCatalogLookupPort;
use App\Domain\Supporting\Menu\Repository\BranchSetRepository;

/**
 * Проверяет, что позиции категории ссылаются на существующие товары каталога и наборы филиала.
 */
final class BranchCategoryItemsValidator
{
    public function __construct(
        private readonly MenuCatalogLookupPort $catalog,
        private readonly BranchSetRepository $sets,
    ) {}

    /**
     * @param  list<array{entry_kind: string, entry_id: int, sort_order: int}>  $items
     */
    public function assertValid(int $branchId, array $items): void
    {
        foreach ($items as $item) {
            $kind = BranchMenuEntryKind::tryFrom($item['entry_kind']);
            $entryId = $item['entry_id'];

            switch ($kind) {
                case BranchMenuEntryKind::Product:
                    if (! $this->catalog->productExists($entryId)) {
                        throw new \InvalidArgumentException('Товар каталога не найден: '.$entryId.'.');
                    }
                    break;
                case BranchMenuEntryKind::Set:
                    if ($this->sets->findById($branchId, $entryId) === null) {
                        throw new \InvalidArgumentException('Набор филиала не найден: '.$entryId.'.');
                    }
                    break;
                case null:
                    throw new \InvalidArgumentException('Некорректный entry_kind позиции категории.');
                default:
                    /** @var never $kind */
                    throw new \LogicException('Неизвестный вид позиции категории.');
            }
        }
    }
}
