import { useCartCommands } from "../../features/shoppingSession/useCartCommands";
import { useCheckoutOrchestrator } from "../../features/orders/useCheckoutOrchestrator";
import { useCheckoutState } from "./useCheckoutState";

export function useCheckoutFlow() {
    const cartCommands = useCartCommands();
    const state = useCheckoutState();
    const commands = useCheckoutOrchestrator(state);

    return {
        cartStore: cartCommands.cartStore,
        ...state,
        ...commands,
    };
}
