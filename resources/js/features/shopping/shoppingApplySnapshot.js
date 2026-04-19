import { useCartStore } from "../../stores/cartStore";
import { useFavoritesStore } from "../../stores/favoritesStore";
import { useOrderStore } from "../../stores/orderStore";

export function applyShoppingSnapshotToStores(data) {
    if (!data || typeof data !== "object") return;

    const cartStore = useCartStore();
    const favoritesStore = useFavoritesStore();
    const orderStore = useOrderStore();

    cartStore.applyServerSnapshot(data.cart ?? {});
    favoritesStore.applyServerSnapshot(data.favorites ?? []);
    orderStore.applyCheckoutDraftFromServer(data.checkout_draft ?? null);
}
