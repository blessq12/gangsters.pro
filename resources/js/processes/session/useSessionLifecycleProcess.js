import { DOMAIN_EVENTS, subscribeDomainEvent } from "../../shared/domainEvents";
import { useCheckoutPricingStore } from "../../stores/checkoutPricingStore";
import { useCheckoutStore } from "../../stores/checkoutStore";
import { useCartCommands } from "../../features/shoppingSession/useCartCommands";
import { useUiStore } from "../../stores/uiStore";

let processInitialized = false;
let cleanupHandlers = [];

export function useSessionLifecycleProcess() {
    if (!processInitialized) {
        const cartCommands = useCartCommands();
        const pricingStore = useCheckoutPricingStore();
        const uiStore = useUiStore();

        cleanupHandlers = [
            subscribeDomainEvent(DOMAIN_EVENTS.CLIENT_LOGGED_OUT, () => {
                useCheckoutStore().clearAfterCompleted();
                pricingStore.$patch({
                    promoState: {},
                    deliveryPricing: null,
                    benefitsProgress: null,
                    loading: false,
                    error: null,
                });
                uiStore.setDockActive(null);
            }),
            subscribeDomainEvent(DOMAIN_EVENTS.ORDER_CREATED, () => {
                useCheckoutStore().clearAfterCompleted();
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
