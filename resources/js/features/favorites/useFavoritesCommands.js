import { useFavoritesStore } from "../../stores/favoritesStore";

export function useFavoritesCommands() {
    const favoritesStore = useFavoritesStore();

    return {
        async toggle(product) {
            await favoritesStore.toggleFavorite(product);
        },
        async remove(productId) {
            await favoritesStore.removeFavorite(productId);
        },
        async clear() {
            await favoritesStore.clear();
        },
    };
}
