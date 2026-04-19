<?php

namespace App\Application\Shopping\Command;

use App\Application\Shopping\Presenter\ShoppingStatePresenter;
use App\Domain\Shopping\Entities\ShoppingSession;
use App\Domain\Shopping\Repositories\ShoppingSessionRepositoryInterface;

final class RemoveFavoriteUseCase
{
    public function __construct(
        private readonly ShoppingSessionRepositoryInterface $sessions,
        private readonly ShoppingStatePresenter $presenter,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(ShoppingSession $session, int $productId): array
    {
        $session->removeFavorite($productId);
        $this->sessions->save($session);

        return $this->presenter->present($session);
    }
}
