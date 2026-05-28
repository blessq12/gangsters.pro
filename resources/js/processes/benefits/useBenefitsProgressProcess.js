import { useToast } from "vue-toastification";
import { useCartStore } from "../../stores/cartStore";
import {
    DOMAIN_EVENTS,
    emitDomainEvent,
    subscribeDomainEvent,
} from "../../shared/domainEvents";

let processInitialized = false;
let cleanupHandlers = [];
let prevDeliveryReached = null;
let prevGiftReached = null;

export function useBenefitsProgressProcess() {
    if (!processInitialized) {
        const toast = useToast();
        const cartStore = useCartStore();

        cleanupHandlers = [
            subscribeDomainEvent(DOMAIN_EVENTS.CART_CHANGED, () => {
                const deliveryReached = Boolean(
                    cartStore.benefitsProgress?.delivery?.isReached,
                );
                const giftReached = Boolean(cartStore.benefitsProgress?.gift?.isReached);

                if (prevDeliveryReached === null) {
                    prevDeliveryReached = deliveryReached;
                } else if (prevDeliveryReached !== deliveryReached) {
                    if (deliveryReached) {
                        toast.success("Бесплатная доставка активна");
                        emitDomainEvent(DOMAIN_EVENTS.BENEFIT_DELIVERY_REACHED, {
                            source: "snapshot",
                        });
                    } else {
                        toast.info("Условие бесплатной доставки больше не выполнено");
                        emitDomainEvent(DOMAIN_EVENTS.BENEFIT_DELIVERY_LOST, {
                            source: "snapshot",
                        });
                    }
                    prevDeliveryReached = deliveryReached;
                }

                if (prevGiftReached === null) {
                    prevGiftReached = giftReached;
                } else if (prevGiftReached !== giftReached) {
                    if (giftReached) {
                        toast.success("Подарок доступен");
                        emitDomainEvent(DOMAIN_EVENTS.BENEFIT_GIFT_UNLOCKED, {
                            source: "snapshot",
                        });
                    } else {
                        toast.info("Условие подарка больше не выполнено");
                        emitDomainEvent(DOMAIN_EVENTS.BENEFIT_GIFT_LOST, {
                            source: "snapshot",
                        });
                    }
                    prevGiftReached = giftReached;
                }
            }),
        ];

        processInitialized = true;
    }

    return {
        dispose() {
            cleanupHandlers.forEach((cleanup) => cleanup());
            cleanupHandlers = [];
            prevDeliveryReached = null;
            prevGiftReached = null;
            processInitialized = false;
        },
    };
}
