import { computed } from "vue";
import { resolveSelectedGiftSummary } from "../../domain/order/normalizeCheckoutCart";
import { useCheckoutSession } from "./useCheckoutSession";
import { useCheckoutStore } from "../../stores/checkoutStore";

function resolveGiftNameFromPromo(promoState, productId) {
    const giftPromotion = promoState?.gift_promotion;
    const candidateItems = Array.isArray(giftPromotion?.candidate_items)
        ? giftPromotion.candidate_items
        : [];
    const candidate = candidateItems.find((item) => Number(item?.id) === productId);

    return candidate?.name ? String(candidate.name) : `Товар #${productId}`;
}

export function useSelectedGiftSummary() {
    const checkoutSession = useCheckoutSession();
    const checkoutStore = useCheckoutStore();

    return computed(() => {
        const promoState = checkoutSession.promoState.value;
        const cartItems = checkoutSession.items.value;

        const fromCartOrPromo = resolveSelectedGiftSummary({
            cartItems,
            promoState,
        });
        if (fromCartOrPromo) {
            return fromCartOrPromo;
        }

        const productId = Number(checkoutStore.promotions?.freeRollGiftProductId) || 0;
        if (productId <= 0) {
            return null;
        }

        return {
            productId,
            name: resolveGiftNameFromPromo(promoState, productId),
            qty: 1,
        };
    });
}
