import {
    playFlyProductToCart,
    resolveCartFlyTargetEl,
} from "../../animations/animationManager";
import { DOMAIN_EVENTS, subscribeDomainEvent } from "../../shared/domainEvents";

function runFlyFromPayload(payload) {
    const sourceEl = payload?.flySourceEl;
    if (!sourceEl?.isConnected) return;

    playFlyProductToCart({
        sourceEl,
        imageUrl: payload?.flyImageUrl,
        targetEl: resolveCartFlyTargetEl(),
    });
}

/**
 * Shell-level: fly превью к dock cart на CART_ADD / INCREMENT с каталога.
 */
export function useCartFlyToDockAnimation() {
    const unsubAdd = subscribeDomainEvent(
        DOMAIN_EVENTS.CART_ADD_REQUESTED,
        (payload) => {
            if (payload?.source !== "catalog") return;
            runFlyFromPayload(payload);
        },
    );

    const unsubInc = subscribeDomainEvent(
        DOMAIN_EVENTS.CART_INCREMENT_REQUESTED,
        (payload) => {
            if (payload?.source !== "catalog") return;
            runFlyFromPayload(payload);
        },
    );

    return {
        dispose() {
            unsubAdd();
            unsubInc();
        },
    };
}
