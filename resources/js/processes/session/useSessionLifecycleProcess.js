import { DOMAIN_EVENTS, subscribeDomainEvent } from "../../shared/domainEvents";
import { useCartCommands } from "../../features/shoppingSession/useCartCommands";
import { useFavoritesCommands } from "../../features/favorites/useFavoritesCommands";
import { useUiStore } from "../../stores/uiStore";

let processInitialized = false;
let cleanupHandlers = [];

export function useSessionLifecycleProcess() {
    if (!processInitialized) {
        const cartCommands = useCartCommands();
        const favoritesCommands = useFavoritesCommands();
        const uiStore = useUiStore();

        cleanupHandlers = [
            subscribeDomainEvent(DOMAIN_EVENTS.CLIENT_LOGGED_OUT, () => {
                cartCommands.clearCart();
                favoritesCommands.clear();
                uiStore.setDockActive(null);
            }),
            subscribeDomainEvent(DOMAIN_EVENTS.ORDER_CREATED, () => {
                cartCommands.clearCart();
            }),
        ];

        processInitialized = true;
    }

    return {
        dispose() {
            cleanupHandlers.forEach((cleanup) => cleanup());
            cleanupHandlers = [];
            processInitialized = false;
        },
    };
}
