import { DOMAIN_EVENTS, subscribeDomainEvent } from "../../shared/domainEvents";
import { mergeShoppingAfterAuth } from "../../features/shopping/shoppingBootstrap";
import { useCartStore } from "../../stores/cartStore";
import { useFavoritesStore } from "../../stores/favoritesStore";
import { useCartCommands } from "../../features/shoppingSession/useCartCommands";
import { useUiStore } from "../../stores/uiStore";
import { resetCheckoutAfterOrderCompleted } from "../../features/checkout/resetCheckoutAfterOrderCompleted";

let processInitialized = false;
let cleanupHandlers = [];

export function useSessionLifecycleProcess() {
    if (!processInitialized) {
        const cartCommands = useCartCommands();
        const cartStore = useCartStore();
        const favoritesStore = useFavoritesStore();
        const uiStore = useUiStore();

        cleanupHandlers = [
            subscribeDomainEvent(DOMAIN_EVENTS.CLIENT_LOGGED_IN, () => {
                void mergeShoppingAfterAuth();
            }),
            subscribeDomainEvent(DOMAIN_EVENTS.CLIENT_LOGGED_OUT, () => {
                cartStore.$patch({
                    cartItems: [],
                    subtotalKopecks: 0,
                    subtotalUserKopecks: 0,
                    subtotalSystemKopecks: 0,
                    promoState: {},
                    deliveryPricing: null,
                    benefitsProgress: null,
                    loading: false,
                    error: null,
                });
                favoritesStore.$patch({ items: [], loading: false, error: null });
                uiStore.setDockActive(null);
            }),
            subscribeDomainEvent(DOMAIN_EVENTS.ORDER_CREATED, () => {
                resetCheckoutAfterOrderCompleted();
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
