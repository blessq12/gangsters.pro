import { useCartStore } from "../../stores/cartStore";
import { useUiStore } from "../../stores/uiStore";
import { DOMAIN_EVENTS, subscribeDomainEvent } from "../../shared/domainEvents";

let processInitialized = false;
let cleanupHandlers = [];

function isHomeRoute() {
    if (typeof window === "undefined") return false;
    return window.location.pathname === "/";
}

function canAutoOpenGiftModal(cartStore, uiStore) {
    if (!isHomeRoute()) {
        return false;
    }
    if (uiStore.giftAutoPromptDismissed) {
        return false;
    }
    const promo = cartStore.promoState?.gift_promotion;
    if (!promo || typeof promo !== "object") {
        return false;
    }
    return promo.eligible === true && promo.phase === "select_gift";
}

export function useGiftAutoPromptProcess() {
    if (!processInitialized) {
        const cartStore = useCartStore();
        const uiStore = useUiStore();

        cleanupHandlers = [
            subscribeDomainEvent(DOMAIN_EVENTS.BENEFIT_GIFT_UNLOCKED, () => {
                if (!canAutoOpenGiftModal(cartStore, uiStore)) {
                    return;
                }
                uiStore.openGiftSelectionModal({ source: "auto" });
            }),
            subscribeDomainEvent(DOMAIN_EVENTS.BENEFIT_GIFT_LOST, () => {
                uiStore.resetGiftAutoPromptDismissed();
                uiStore.closeGiftSelectionModal({ dismissAuto: false });
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
