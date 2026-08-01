import { DOMAIN_EVENTS, emitDomainEvent, subscribeDomainEvent } from "../../shared/domainEvents";
import { useCheckoutStore } from "../../stores/checkoutStore";
import { useUiStore } from "../../stores/uiStore";

let processInitialized = false;
let cleanupHandlers = [];

export function useSessionLifecycleProcess() {
    if (!processInitialized) {
        const uiStore = useUiStore();

        cleanupHandlers = [
            subscribeDomainEvent(DOMAIN_EVENTS.CLIENT_LOGGED_OUT, () => {
                useCheckoutStore().clearAfterCompleted();
                uiStore.setDockActive(null);
            }),
            subscribeDomainEvent(DOMAIN_EVENTS.ORDER_CREATED, () => {
                useCheckoutStore().clearAfterCompleted();
                uiStore.closeGiftSelectionModal({ dismissAuto: false });
                uiStore.resetGiftAutoPromptDismissed();
                emitDomainEvent(DOMAIN_EVENTS.CART_CLEARED);
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
