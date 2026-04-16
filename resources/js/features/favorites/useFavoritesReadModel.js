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
