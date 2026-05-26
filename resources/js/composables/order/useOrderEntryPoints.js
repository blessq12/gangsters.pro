import { computed } from "vue";
import { useRoute } from "vue-router";
import { useCartStore } from "../../stores/cartStore";
import { useUiStore } from "../../stores/uiStore";
import { formatMoneyRublesRu } from "../../utils/moneyFormat";

/**
 * UI entry points: корзина (dock cart) и старт оформления без дублирования шагов checkout.
 */
export function useOrderEntryPoints() {
    const uiStore = useUiStore();
    const cartStore = useCartStore();
    const route = useRoute();
    const cartSummary = computed(() => ({
        count: cartStore.cartTotalItems,
        amountRub: formatMoneyRublesRu(cartStore.cartTotalAmount),
    }));

    function ensureDockChromeVisible() {
        uiStore.setShowBottomNav(true);
        uiStore.setMobileDockScrollSuppressed(false);
    }

    function openCart() {
        ensureDockChromeVisible();
        if (uiStore.dockActiveId !== "cart") {
            uiStore.setDockActive("cart");
        }
    }

    function openProfileDock() {
        ensureDockChromeVisible();
        if (uiStore.dockActiveId !== "profile") {
            uiStore.setDockActive("profile");
        }
    }

    function startCheckout() {
        openCart();
        uiStore.requestCheckoutStart();
    }

    const isHome = computed(() => route.name === "home");

    return {
        cartSummary,
        isHome,
        openCart,
        openProfileDock,
        startCheckout,
    };
}
