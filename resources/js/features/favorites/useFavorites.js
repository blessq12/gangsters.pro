import { computed } from "vue";
import { useFavoritesStore } from "../../stores/favoritesStore";

export function useFavoritesReadModel() {
    const favoritesStore = useFavoritesStore();

    return {
        items: computed(() => favoritesStore.favorites),
        count: computed(() => favoritesStore.count),
        isFavorite(productId) {
            return favoritesStore.isFavorite(productId);
        },
    };
}

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
