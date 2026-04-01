import { computed } from "vue";

export function useBottomDockState({ uiStore, cartStore, favoritesStore, dockItems }) {
    const safeDockItems = Array.isArray(dockItems) ? dockItems : [];
    const activeDockItem = computed(() =>
        safeDockItems.find((item) => item.id === uiStore.dockActiveId) || null,
    );

    const resolvedDockBadges = computed(() =>
        uiStore.resolvedDockBadges(cartStore.cartTotalItems, favoritesStore.count),
    );

    const getBadge = (id) => resolvedDockBadges.value?.[id] ?? 0;

    return {
        activeDockItem,
        getBadge,
        dockItems: safeDockItems,
    };
}

