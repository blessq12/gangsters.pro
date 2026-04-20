<?php

namespace App\Application\Shopping\Presenter;

use App\Application\Shopping\CartRules\ResolveShoppingCartUseCase;
use App\Domain\Order\Contracts\CatalogItemSnapshotProvider;
use App\Domain\Shopping\CartRules\CartLineItem;
use App\Domain\Shopping\Entities\ShoppingSession;
use App\Support\Money;

final class ShoppingStatePresenter
{
    public function __construct(
        private readonly CatalogItemSnapshotProvider $catalog,
        private readonly ResolveShoppingCartUseCase $resolveShoppingCart,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(ShoppingSession $session): array
    {
        $resolved = $this->resolveShoppingCart->execute($session);
        $lines = array_merge($resolved->userLines, $resolved->systemLines);
        $productIds = array_unique(array_map(static fn (CartLineItem $l) => $l->productId, $lines));
        $snapshots = $productIds !== [] ? $this->catalog->getActiveSnapshotsByIds($productIds) : [];

        $cartItems = [];
        $subtotalKopecks = 0;
        foreach ($lines as $line) {
            $snap = $snapshots[$line->productId] ?? null;
            if ($snap === null) {
                continue;
            }
            $finalUnitKopecks = $line->finalUnitPriceKopecks ?? 0;
            $lineTotal = $finalUnitKopecks * $line->quantity;
            $subtotalKopecks += $lineTotal;
            $cartItems[] = [
                'productId' => $line->productId,
                'qty' => $line->quantity,
                'line_key' => $line->lineKey,
                'origin' => $line->origin->value,
                'productSnapshot' => [
                    'id' => $line->productId,
                    'name' => (string) $snap['name'],
                    'price' => Money::kopecksToApiRubles($finalUnitKopecks),
                    'weight' => null,
                ],
                'pricing' => [
                    'list_unit_price_kopecks' => (int) $snap['price'],
                    'final_unit_price_kopecks' => $finalUnitKopecks,
                    'line_total_kopecks' => $lineTotal,
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
                'promo_state' => $resolved->promoState,
                'subtotal_user_kopecks' => $resolved->subtotalUserKopecks,
                'subtotal_system_kopecks' => $resolved->subtotalSystemKopecks,
            ],
            'favorites' => $favorites,
            'checkout_draft' => $session->getCheckoutDraft(),
        ];
    }
}
