<?php

namespace App\Application\Shopping\Presenter;

use App\Domain\Order\Contracts\CatalogItemSnapshotProvider;
use App\Domain\Shopping\Entities\CartLine;
use App\Domain\Shopping\Entities\ShoppingSession;
use App\Support\Money;

final class ShoppingStatePresenter
{
    public function __construct(
        private readonly CatalogItemSnapshotProvider $catalog,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function present(ShoppingSession $session): array
    {
        $cartLines = $session->getCartLines();
        $productIds = array_unique(array_map(static fn (CartLine $l) => $l->productId, $cartLines));
        $snapshots = $productIds !== [] ? $this->catalog->getActiveSnapshotsByIds($productIds) : [];

        $cartItems = [];
        $subtotalKopecks = 0;
        foreach ($cartLines as $line) {
            $snap = $snapshots[$line->productId] ?? null;
            if ($snap === null) {
                continue;
            }
            $lineTotal = (int) $snap['price'] * $line->quantity;
            $subtotalKopecks += $lineTotal;
            $cartItems[] = [
                'productId' => $line->productId,
                'qty' => $line->quantity,
                'productSnapshot' => [
                    'id' => $line->productId,
                    'name' => (string) $snap['name'],
                    'price' => Money::kopecksToApiRubles((int) $snap['price']),
                    'weight' => null,
                ],
            ];
        }

        $favoriteIds = $session->getFavoriteProductIds();
        $favSnapshots = $favoriteIds !== [] ? $this->catalog->getActiveSnapshotsByIds($favoriteIds) : [];
        $favorites = [];
        foreach ($favoriteIds as $pid) {
            $snap = $favSnapshots[$pid] ?? null;
            if ($snap === null) {
                continue;
            }
            $favorites[] = [
                'productId' => $pid,
                'productSnapshot' => [
                    'id' => $pid,
                    'name' => (string) $snap['name'],
                    'price' => Money::kopecksToApiRubles((int) $snap['price']),
                    'weight' => null,
                ],
            ];
        }

        return [
            'session' => [
                'public_id' => $session->getPublicId(),
                'client_id' => $session->getClientId(),
                'expires_at' => $session->getExpiresAt()->format(DATE_ATOM),
            ],
            'cart' => [
                'items' => $cartItems,
                'subtotal_rub' => Money::kopecksToApiRubles($subtotalKopecks),
                'subtotal_kopecks' => $subtotalKopecks,
            ],
            'favorites' => $favorites,
            'checkout_draft' => $session->getCheckoutDraft(),
        ];
    }
}
