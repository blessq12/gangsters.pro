<?php

namespace App\Application\Shopping\Query;

use App\Application\Shopping\Presenter\ShoppingStatePresenter;
use App\Domain\Shopping\Entities\ShoppingSession;

final class GetShoppingStateUseCase
{
    public function __construct(
        private readonly ShoppingStatePresenter $presenter,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(ShoppingSession $session): array
    {
        return $this->presenter->present($session);
    }
}
