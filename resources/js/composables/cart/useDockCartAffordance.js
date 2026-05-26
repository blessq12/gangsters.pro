import { watch } from "vue";
import { useRoute } from "vue-router";
import { DOMAIN_EVENTS, subscribeDomainEvent } from "../../shared/domainEvents";
import { useCartStore } from "../../stores/cartStore";
import { useUiStore } from "../../stores/uiStore";
import { ensureDockChromeVisible } from "../ui/dockChromePolicy";

/**
 * Home mobile: при корзине N>0 не скрываем dock; при первом add — показываем chrome до fly/add.
 */
export function useDockCartAffordance() {
    const uiStore = useUiStore();
    const cartStore = useCartStore();
    const route = useRoute();

    const isHome = () => route.name === "home";

    const unsubAdd = subscribeDomainEvent(
        DOMAIN_EVENTS.CART_ADD_REQUESTED,
        (payload) => {
            if (payload?.source !== "catalog") return;
            if (!isHome()) return;
            ensureDockChromeVisible(uiStore);
        },
    );

    const stopCartCount = watch(
        () => cartStore.cartTotalItems,
        (count, prev) => {
            if (!isHome()) return;
            if (count > 0 && (prev === 0 || prev == null)) {
                ensureDockChromeVisible(uiStore);
            }
        },
    );

    return {
        dispose() {
            unsubAdd();
            stopCartCount();
        },
    };
}
