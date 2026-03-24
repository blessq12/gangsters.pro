import { inject, provide } from "vue";

export const CHECKOUT_FLOW_KEY = Symbol("checkoutFlow");

export function provideCheckoutFlow(flow) {
    provide(CHECKOUT_FLOW_KEY, flow);
}

export function useCheckoutFlowContext() {
    const flow = inject(CHECKOUT_FLOW_KEY);
    if (!flow) {
        throw new Error(
            "useCheckoutFlowContext: ожидается provideCheckoutFlow() в CartDockPanel",
        );
    }
    return flow;
}
