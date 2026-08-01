import { watch } from "vue";
import { playDockTabBump } from "../../animations/animationManager";
import { useFavoritesStore } from "../../stores/favoritesStore";

/**
 * Bump dock badges when favorites count grows.
 * Cart uses DockCartSummary flash instead of tab bump.
 */
export function useDockBadgeFeedback() {
    const favoritesStore = useFavoritesStore();

    const stopFav = watch(
        () => favoritesStore.count,
        (count, prev) => {
            if (prev == null || count <= prev) return;
            playDockTabBump("favorites");
        },
    );

    return {
        dispose() {
            stopFav();
        },
    };
}
