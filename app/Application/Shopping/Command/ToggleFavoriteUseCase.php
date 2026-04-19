<?php

namespace App\Application\Shopping\Command;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Shopping\Presenter\ShoppingStatePresenter;
use App\Domain\Order\Contracts\CatalogItemSnapshotProvider;
use App\Domain\Shopping\Entities\ShoppingSession;
use App\Domain\Shopping\Repositories\ShoppingSessionRepositoryInterface;

final class ToggleFavoriteUseCase
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
    public function execute(ShoppingSession $session, int $productId): array
    {
        $snapshots = $this->catalog->getActiveSnapshotsByIds([$productId]);
        if (! isset($snapshots[$productId])) {
            throw new ApiException('Product is not available.');
        }

        if ($session->hasFavorite($productId)) {
            $session->removeFavorite($productId);
        } else {
            $session->addFavorite($productId);
        }

        $this->sessions->save($session);

        return $this->presenter->present($session);
    }
}
