<?php

namespace App\Application\Shopping\Command;

use App\Application\Shopping\Presenter\ShoppingStatePresenter;
use App\Domain\Order\Contracts\CatalogItemSnapshotProvider;
use App\Domain\Shopping\Entities\CartLine;
use App\Domain\Shopping\Entities\ShoppingSession;
use App\Domain\Shopping\Repositories\ShoppingSessionRepositoryInterface;

/**
 * Удаляет строки корзины без активного товара в каталоге; точка расширения под промо.
 */
final class RecalculateShoppingCartUseCase
{
    public function __construct(
        private readonly ShoppingSessionRepositoryInterface $sessions,
        private readonly CatalogItemSnapshotProvider $catalog,
        private readonly ShoppingStatePresenter $presenter,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(ShoppingSession $session): array
    {
        $lines = $session->getCartLines();
        if ($lines === []) {
            return $this->presenter->present($session);
        }

        $ids = array_unique(array_map(static fn (CartLine $l) => $l->productId, $lines));
        $snapshots = $this->catalog->getActiveSnapshotsByIds($ids);

        $changed = false;
        foreach ($lines as $line) {
            if (! isset($snapshots[$line->productId])) {
                $session->removeCartLine($line->productId);
                $changed = true;
            }
        }

        if ($changed) {
            $this->sessions->save($session);
        }

        return $this->presenter->present($session);
    }
}
