import { computed } from "vue";
import { useCheckoutStore } from "../../stores/checkoutStore";
import { useCartReadModel } from "../shoppingSession/useCartReadModel";
import { resolveComplementWizardOffers } from "./resolveComplementWizardOffers";

export function useComplementWizardOffers() {
    const cartReadModel = useCartReadModel();
    const checkoutStore = useCheckoutStore();

    const offers = computed(() =>
        resolveComplementWizardOffers({
            systemItems: cartReadModel.systemItems.value,
            userItems: cartReadModel.userItems.value,
            promoState: cartReadModel.promoState.value,
        }),
    );

    async function addComplementToUserCart(productId) {
        const id = Number(productId) || 0;
        if (id <= 0) {
            return;
        }

        const existing = checkoutStore.userItems.find((item) => item.productId === id);
        const nextQty = (existing?.qty ?? 0) + 1;
        await checkoutStore.updateCartLine(id, nextQty);
    }

    return {
        offers,
        hasComplementOffers: computed(() => offers.value.length > 0),
        addComplementToUserCart,
    };
}
