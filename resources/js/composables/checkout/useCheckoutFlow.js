import { useCartCommands } from "../../features/shoppingSession/useCartCommands";
import { useCheckout } from "../../features/checkout/useCheckout";

export function useCheckoutFlow() {
    const cartCommands = useCartCommands();
    const checkout = useCheckout();

    return {
        cartStore: cartCommands.cartStore,
        ...checkout,
    };
}
