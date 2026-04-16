import { useCartCommands } from "../../features/shoppingSession/useCartCommands";
import { useCheckoutState } from "./useCheckoutState";
import { useCheckoutCommands } from "./useCheckoutCommands";

export function useCheckoutFlow() {
    const cartCommands = useCartCommands();
    const state = useCheckoutState();
    const commands = useCheckoutCommands(state);

    return {
        cartStore: cartCommands.cartStore,
        ...state,
        ...commands,
    };
}
