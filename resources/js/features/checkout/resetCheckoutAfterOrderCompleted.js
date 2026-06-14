import { useCheckoutStore } from "../../stores/checkoutStore";

/** Сброс checkout после успешного оформления; новый объект — при следующем запуске или первом действии с корзиной. */
export function resetCheckoutAfterOrderCompleted() {
    const checkoutStore = useCheckoutStore();
    checkoutStore.clearAfterCompleted();
}
