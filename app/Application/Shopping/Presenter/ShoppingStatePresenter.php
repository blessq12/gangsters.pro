<?php

namespace App\Application\Shopping\Presenter;

use App\Application\Shopping\CartRules\ResolveShoppingCartUseCase;
use App\Application\Shopping\SuggestedCheckoutStepResolver;
use App\Domain\Order\Contracts\CatalogItemSnapshotProvider;
use App\Domain\Shopping\CartRules\CartLineItem;
use App\Domain\Shopping\Entities\ShoppingSession;
use App\Support\Money;

final class ShoppingStatePresenter
{
    public function __construct(
        private readonly CatalogItemSnapshotProvider $catalog,
        private readonly ResolveShoppingCartUseCase $resolveShoppingCart,
        private readonly SuggestedCheckoutStepResolver $suggestedCheckoutStep,
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
        $promoState = $this->enrichPromoState($resolved->promoState);

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

        $draft = $session->getCheckoutDraft();

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
                'promo_state' => $promoState,
                'subtotal_user_kopecks' => $resolved->subtotalUserKopecks,
                'subtotal_system_kopecks' => $resolved->subtotalSystemKopecks,
            ],
            'favorites' => $favorites,
            'checkout_draft' => $draft,
            'checkout_intent' => $draft,
            'suggested_step' => $this->suggestedCheckoutStep->resolve($session, $resolved),
        ];
    }

    /**
     * @param  array<string, mixed>  $promoState
     * @return array<string, mixed>
     */
    private function enrichPromoState(array $promoState): array
    {
        $gift = $promoState['gift_promotion'] ?? null;
        if (! is_array($gift)) {
            return $promoState;
        }

        $candidateIds = $gift['candidate_product_ids'] ?? [];
        if (! is_array($candidateIds)) {
            $candidateIds = [];
        }
        $candidateIds = array_values(array_unique(array_map(
            static fn ($id): int => (int) $id,
            array_filter($candidateIds, static fn ($id): bool => (int) $id > 0),
        )));

        $candidateSnapshots = $candidateIds !== []
            ? $this->catalog->getActiveSnapshotsByIds($candidateIds)
            : [];

        $candidateItems = [];
        foreach ($candidateIds as $id) {
            $snap = $candidateSnapshots[$id] ?? null;
            if (! is_array($snap)) {
                continue;
            }
            $media = isset($snap['media']) && is_array($snap['media']) ? $snap['media'] : [];
            $imageUrl = null;
            if ($media !== [] && is_array($media[0] ?? null)) {
                $imageUrl = isset($media[0]['url']) ? (string) $media[0]['url'] : null;
            }
            $priceKopecks = (int) ($snap['price'] ?? 0);
            $candidateItems[] = [
                'id' => $id,
                'name' => (string) ($snap['name'] ?? ''),
                'price_kopecks' => $priceKopecks,
                'price_rub' => Money::kopecksToApiRubles($priceKopecks),
                'image_url' => $imageUrl,
            ];
        }

        $gift['candidate_items'] = $candidateItems;
        $promoState['gift_promotion'] = $gift;

        return $promoState;
    }
}
