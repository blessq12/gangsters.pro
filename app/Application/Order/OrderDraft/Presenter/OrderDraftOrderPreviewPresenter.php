<?php

namespace App\Application\Order\OrderDraft\Presenter;

use App\Application\Order\OrderDraft\Support\PromotionLineClassifier;
use App\Domain\Order\OrderDraft\Entity\OrderDraft;
use App\Domain\Order\OrderDraft\ValueObject\CartLineSnapshot;

/**
 * Read-model превью заказа для UI визарда.
 */
final class OrderDraftOrderPreviewPresenter
{
    /**
     * @param  array{
     *     benefits_progress: array<string, mixed>,
     *     delivery_pricing: array<string, mixed>|null,
     *     promo_state: array<string, mixed>
     * }  $benefits
     * @return array<string, mixed>
     */
    public function present(OrderDraft $draft, array $benefits): array
    {
        $promoState = $benefits['promo_state'];

        return [
            'complement_lines' => $this->presentComplementLines($draft, $promoState),
            'auto_lines' => $this->presentAutoLines($draft),
            'gift_summary' => $this->presentGiftSummary($draft, $promoState),
            'gift_cta' => $this->presentGiftCta($promoState),
            'totals' => $this->presentTotals($draft, $benefits['delivery_pricing']),
            'benefits' => [
                'delivery' => $benefits['benefits_progress']['delivery'] ?? null,
                'gift' => $benefits['benefits_progress']['gift'] ?? null,
                'complement' => $benefits['benefits_progress']['complement'] ?? null,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $promoState
     * @return list<array<string, mixed>>
     */
    private function presentComplementLines(OrderDraft $draft, array $promoState): array
    {
        $fromCart = [];
        foreach ($draft->cart()->lines() as $line) {
            if (! PromotionLineClassifier::isComplementLine($line)) {
                continue;
            }

            $fromCart[] = $this->presentPreviewLine($line, isFree: true);
        }

        if ($fromCart !== []) {
            return $fromCart;
        }

        $complementPromotion = $promoState['complement_promotion'] ?? null;
        if (! is_array($complementPromotion) || ($complementPromotion['eligible'] ?? false) !== true) {
            return [];
        }

        $quantity = (int) ($complementPromotion['entitled_set_count'] ?? 0);
        if ($quantity <= 0) {
            return [];
        }

        $lines = [];
        foreach ($complementPromotion['candidate_items'] ?? [] as $candidate) {
            if (! is_array($candidate)) {
                continue;
            }

            $productId = (int) ($candidate['id'] ?? 0);
            if ($productId <= 0) {
                continue;
            }

            $lines[] = [
                'product_id' => $productId,
                'name' => (string) ($candidate['name'] ?? ''),
                'quantity' => $quantity,
                'price_rubles' => (float) ($candidate['price_rub'] ?? 0),
                'is_free' => true,
            ];
        }

        return $lines;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function presentAutoLines(OrderDraft $draft): array
    {
        $lines = [];

        foreach ($draft->cart()->lines() as $line) {
            $kind = $line->lineKind();
            if (in_array($kind, ['user', 'gift', 'complement'], true)) {
                continue;
            }

            $lines[] = $this->presentPreviewLine($line, isFree: true);
        }

        return $lines;
    }

    /**
     * @param  array<string, mixed>  $promoState
     * @return array<string, mixed>|null
     */
    private function presentGiftSummary(OrderDraft $draft, array $promoState): ?array
    {
        foreach ($draft->cart()->lines() as $line) {
            if (! PromotionLineClassifier::isGiftLine($line)) {
                continue;
            }

            return [
                'product_id' => $line->productId(),
                'name' => $line->productName(),
                'quantity' => $line->quantity(),
            ];
        }

        $giftPromotion = $promoState['gift_promotion'] ?? null;
        if (! is_array($giftPromotion)) {
            return null;
        }

        $selectedProductId = (int) ($giftPromotion['selected_product_id'] ?? 0);
        if ($selectedProductId <= 0) {
            return null;
        }

        $name = $this->resolveGiftCandidateName($giftPromotion, $selectedProductId);

        return [
            'product_id' => $selectedProductId,
            'name' => $name,
            'quantity' => 1,
        ];
    }

    /**
     * @param  array<string, mixed>  $promoState
     * @return array<string, mixed>|null
     */
    private function presentGiftCta(array $promoState): ?array
    {
        $giftPromotion = $promoState['gift_promotion'] ?? null;
        if (! is_array($giftPromotion)) {
            return null;
        }

        return [
            'eligible' => (bool) ($giftPromotion['eligible'] ?? false),
            'phase' => (string) ($giftPromotion['phase'] ?? 'below_threshold'),
            'selected_product_id' => $giftPromotion['selected_product_id'] ?? null,
            'candidate_items' => array_values(
                is_array($giftPromotion['candidate_items'] ?? null)
                    ? $giftPromotion['candidate_items']
                    : [],
            ),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $deliveryPricing
     * @return array<string, mixed>
     */
    private function presentTotals(OrderDraft $draft, ?array $deliveryPricing): array
    {
        $itemsTotalRubles = $draft->cart()->payableTotal()->amountRubles();

        if (! is_array($deliveryPricing)) {
            return [
                'items_total_rubles' => $itemsTotalRubles,
                'delivery_fee_rubles' => null,
                'base_delivery_fee_rubles' => null,
                'outside_zone_surcharge_rubles' => null,
                'grand_total_rubles' => $itemsTotalRubles,
                'is_delivery_free' => false,
                'is_delivery_preview' => false,
                'in_zone' => null,
            ];
        }

        return [
            'items_total_rubles' => (float) ($deliveryPricing['items_total_rub'] ?? $itemsTotalRubles),
            'delivery_fee_rubles' => array_key_exists('delivery_fee_rub', $deliveryPricing)
                ? (float) $deliveryPricing['delivery_fee_rub']
                : null,
            'base_delivery_fee_rubles' => array_key_exists('base_delivery_fee_rub', $deliveryPricing)
                ? (float) $deliveryPricing['base_delivery_fee_rub']
                : null,
            'outside_zone_surcharge_rubles' => array_key_exists('outside_zone_surcharge_rub', $deliveryPricing)
                ? (float) $deliveryPricing['outside_zone_surcharge_rub']
                : null,
            'grand_total_rubles' => (float) ($deliveryPricing['grand_total_rub'] ?? $itemsTotalRubles),
            'is_delivery_free' => (bool) ($deliveryPricing['is_free'] ?? false),
            'is_delivery_preview' => (bool) ($deliveryPricing['is_preview'] ?? false),
            'in_zone' => array_key_exists('in_zone', $deliveryPricing)
                ? $deliveryPricing['in_zone']
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentPreviewLine(CartLineSnapshot $line, bool $isFree): array
    {
        return [
            'product_id' => $line->productId(),
            'name' => $line->productName(),
            'quantity' => $line->quantity(),
            'price_rubles' => $line->unitPrice()->amountRubles(),
            'is_free' => $isFree,
        ];
    }

    /**
     * @param  array<string, mixed>  $giftPromotion
     */
    private function resolveGiftCandidateName(array $giftPromotion, int $selectedProductId): string
    {
        foreach ($giftPromotion['candidate_items'] ?? [] as $candidate) {
            if (! is_array($candidate)) {
                continue;
            }

            if ((int) ($candidate['id'] ?? 0) === $selectedProductId) {
                $name = trim((string) ($candidate['name'] ?? ''));

                return $name !== '' ? $name : 'Товар #'.$selectedProductId;
            }
        }

        return 'Товар #'.$selectedProductId;
    }
}
