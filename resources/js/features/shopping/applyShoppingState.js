import { useCartStore } from "../../stores/cartStore";
import { useCheckoutIntentStore } from "../../stores/checkoutIntentStore";
import { useFavoritesStore } from "../../stores/favoritesStore";

/**
 * @param {object} cartStore — Pinia cart store instance
 * @param {object} data
 */
export function applyCartAndDeliveryFromState(cartStore, data) {
    if (!data || typeof data !== "object") return;

    cartStore.applyServerSnapshot(data.cart ?? null);
    if (Object.prototype.hasOwnProperty.call(data, "delivery_pricing")) {
        cartStore.applyDeliveryPricingSnapshot(data.delivery_pricing);
    }
}

/**
 * @param {object} data
 */
export function applyShoppingMetaFromState(data) {
    if (!data || typeof data !== "object") return;

    const favoritesStore = useFavoritesStore();
    const intentStore = useCheckoutIntentStore();

    favoritesStore.applyServerSnapshot(data.favorites ?? null);
    intentStore.applyFromServer(
        data.checkout_intent ?? data.checkout_draft ?? null,
        data.cart?.promo_state ?? null,
    );

    if (Object.prototype.hasOwnProperty.call(data, "suggested_step")) {
        const step = data.suggested_step;
        if (step === "cart" || step == null) {
            intentStore.setSuggestedStep(null);
        } else {
            intentStore.setSuggestedStep(step);
        }
    }
}

/**
 * @param {object|null|undefined} data
 */
export function applyShoppingSnapshotToStores(data) {
    if (!data || typeof data !== "object") return;

    const cartStore = useCartStore();
    applyCartAndDeliveryFromState(cartStore, data);
    applyShoppingMetaFromState(data);
}
