<?php

namespace App\Application\Shopping\Command;

use App\Application\Shopping\Presenter\ShoppingStatePresenter;
use App\Domain\Shopping\Entities\ShoppingSession;
use App\Domain\Shopping\Repositories\ShoppingSessionRepositoryInterface;

/**
 * Одноразовый импорт из localStorage (корзина/избранное/черновик) в серверную сессию.
 */
final class MigrateLocalShoppingStateUseCase
{
    public function __construct(
        private readonly ShoppingSessionRepositoryInterface $sessions,
        private readonly ShoppingStatePresenter $presenter,
    ) {
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function execute(ShoppingSession $session, array $payload): array
    {
        if (isset($payload['cart_items']) && is_array($payload['cart_items'])) {
            foreach ($payload['cart_items'] as $row) {
                $pid = isset($row['productId']) ? (int) $row['productId'] : (isset($row['product_id']) ? (int) $row['product_id'] : 0);
                $qty = isset($row['qty']) ? (int) $row['qty'] : (isset($row['quantity']) ? (int) $row['quantity'] : 0);
                if ($pid > 0 && $qty > 0) {
                    $session->upsertCartLine($pid, $qty, null);
                }
            }
        }

        if (isset($payload['favorite_product_ids']) && is_array($payload['favorite_product_ids'])) {
            foreach ($payload['favorite_product_ids'] as $pid) {
                $id = (int) $pid;
                if ($id > 0) {
                    $session->addFavorite($id);
                }
            }
        }

        if (isset($payload['checkout_draft']) && is_array($payload['checkout_draft'])) {
            $session->setCheckoutDraft($payload['checkout_draft']);
        }

        $this->sessions->save($session);

        return $this->presenter->present($session);
    }
}
