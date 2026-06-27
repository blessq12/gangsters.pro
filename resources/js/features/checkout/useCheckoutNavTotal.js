import { computed, unref } from "vue";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import { useOrderPreview } from "./useOrderPreview";

/** Компактная сумма для nav на экране fulfillment. */
export function useCheckoutNavTotal() {
    const { checkoutState } = useCheckoutFlowContext();
    const { formatPrice } = checkoutState;
    const { displayGrandTotalRubles, hasCartItems } = useOrderPreview();

    const navTotalLabel = computed(() => {
        if (!unref(hasCartItems)) {
            return "";
        }
        const rubles = unref(displayGrandTotalRubles);
        if (!Number.isFinite(rubles)) {
            return "";
        }
        return `${formatPrice(rubles)} ₽`;
    });

    return { navTotalLabel };
}
