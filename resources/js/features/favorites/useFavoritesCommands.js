import { useFavoritesStore } from "../../stores/favoritesStore";

export function useFavoritesCommands() {
    const favoritesStore = useFavoritesStore();

    return {
        toggle(product) {
            favoritesStore.toggleFavorite(product);
        },
        remove(productId) {
            favoritesStore.removeFavorite(productId);
        },
        clear() {
            favoritesStore.clear();
        },
    };
}
