import { computed } from "vue";
import { resolveSelectedGiftSummary } from "./normalizeCheckoutCart";
import { useCartReadModel } from "../shoppingSession/useCartReadModel";

export function useSelectedGiftSummary() {
    const cartReadModel = useCartReadModel();

    return computed(() =>
        resolveSelectedGiftSummary({
            cartItems: cartReadModel.items.value,
            promoState: cartReadModel.promoState.value,
        }),
    );
}
