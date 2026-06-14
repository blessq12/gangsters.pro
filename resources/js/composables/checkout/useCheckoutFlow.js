import { useCheckout } from "../../features/checkout/useCheckout";

/** @deprecated Используйте useCheckout */
export function useCheckoutFlow() {
    return useCheckout();
}
