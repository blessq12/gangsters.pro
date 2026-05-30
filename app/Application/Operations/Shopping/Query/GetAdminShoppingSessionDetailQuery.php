<?php

namespace App\Application\Operations\Shopping\Query;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Shopping\Presenter\AdminShoppingSessionPresenter;
use App\Domain\Shopping\Repositories\ShoppingSessionRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class GetAdminShoppingSessionDetailQuery
{
    public function __construct(
        private readonly ShoppingSessionRepositoryInterface $sessions,
        private readonly AdminShoppingSessionPresenter $presenter,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(int $sessionId): array
    {
        try {
            $session = $this->sessions->getById($sessionId);
        } catch (ModelNotFoundException) {
            throw new ApiException('Shopping-сессия не найдена.', 404);
        }

        return $this->presenter->present($session);
    }
}
