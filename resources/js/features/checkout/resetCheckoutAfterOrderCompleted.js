import { useCheckoutIntentStore } from "../../stores/checkoutIntentStore";

/** Сброс локального checkout-intent и resume после успешного заказа. */
export function resetCheckoutAfterOrderCompleted() {
    const checkoutIntent = useCheckoutIntentStore();
    checkoutIntent.clearLocal();
    checkoutIntent.setSuggestedStep(null);
}
