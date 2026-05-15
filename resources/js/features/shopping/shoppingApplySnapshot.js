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
    intentStore.applyFromServer(data.checkout_intent ?? data.checkout_draft ?? null);
    if (data.suggested_step) {
        intentStore.setSuggestedStep(data.suggested_step);
    }
}
