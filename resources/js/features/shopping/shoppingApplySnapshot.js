import { useCartStore } from "../../stores/cartStore";
import { useCheckoutIntentStore } from "../../stores/checkoutIntentStore";
import { useFavoritesStore } from "../../stores/favoritesStore";

export function applyShoppingSnapshotToStores(data) {
    if (!data || typeof data !== "object") return;

    const cartStore = useCartStore();
    const favoritesStore = useFavoritesStore();
    const intentStore = useCheckoutIntentStore();

    cartStore.applyServerSnapshot(data.cart ?? null);
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
