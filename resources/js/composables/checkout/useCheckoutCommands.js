import { useCheckoutOrchestrator } from "../../features/orders/useCheckoutOrchestrator";

export function useCheckoutCommands(checkoutState) {
    // backward-compatible alias; основная orchestration-логика живет в features/orders/useCheckoutOrchestrator
    return useCheckoutOrchestrator(checkoutState);
}

