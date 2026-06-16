import { watch } from "vue";
import { useRoute } from "vue-router";
import { DOMAIN_EVENTS, subscribeDomainEvent } from "../../shared/domainEvents";
import { useUiStore } from "../../stores/uiStore";
import { ensureDockChromeVisible } from "../ui/dockChromePolicy";

/**
 * Home: при add из каталога — показать dock и сбросить scale до fly/add.
 */
export function useDockCartAffordance() {
    const uiStore = useUiStore();
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

    return {
        dispose() {
            unsubAdd();
        },
    };
}
