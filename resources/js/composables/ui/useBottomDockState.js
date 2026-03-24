import { computed } from "vue";
import { dockItems } from "../../dock/dockRegistry";

export function useBottomDockState({ uiStore, cartStore }) {
    const activeDockItem = computed(() =>
        dockItems.find((item) => item.id === uiStore.dockActiveId) || null,
    );

    const resolvedDockBadges = computed(() =>
        uiStore.resolvedDockBadges(cartStore.cartTotalItems, cartStore.favorites.length),
    );

    const getBadge = (id) => resolvedDockBadges.value?.[id] ?? 0;

    return {
        activeDockItem,
        getBadge,
        dockItems,
    };
}

