import { watch } from "vue";
import {
    consumeSkipNextCartBadgeBump,
    playDockTabBump,
} from "../../animations/animationManager";
import { useCheckoutStore } from "../../stores/checkoutStore";
import { useFavoritesStore } from "../../stores/favoritesStore";

/**
 * Bump бейджей dock при росте счётчиков cart / favorites.
 */
export function useDockBadgeFeedback() {
    const cartStore = useCheckoutStore();
    const favoritesStore = useFavoritesStore();

    const stopCart = watch(
        () => cartStore.cartTotalItems,
        (count, prev) => {
            if (count <= 0 || prev == null || count <= prev) return;
            if (consumeSkipNextCartBadgeBump()) return;
            playDockTabBump("cart");
        },
    );

    const stopFav = watch(
        () => favoritesStore.count,
        (count, prev) => {
            if (prev == null || count <= prev) return;
            playDockTabBump("favorites");
        },
    );

    return {
        dispose() {
            stopCart();
            stopFav();
        },
    };
}
