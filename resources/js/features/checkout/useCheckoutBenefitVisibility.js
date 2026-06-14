import { computed } from "vue";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";

export function useCheckoutBenefitVisibility() {
    const { checkoutState, userStore } = useCheckoutFlowContext();
    const { checkoutIntent, isGuestCheckout } = checkoutState;

    const isDeliveryDataFilled = computed(() => {
        const intent = checkoutIntent?.value ?? checkoutIntent;
        if (!intent || typeof intent !== "object") {
            return false;
        }

        if (intent.serverDelivery?.method) {
            return true;
        }

        const method = intent.deliveryInfo?.method;
        if (!method) {
            return false;
        }

        if (method === "pickup") {
            return true;
        }

        const guestFlow = isGuestCheckout?.value ?? isGuestCheckout;
        if (guestFlow) {
            const address = intent.deliveryInfo?.address;
            return (
                String(address?.street || "").trim() !== "" &&
                String(address?.house || "").trim() !== ""
            );
        }

        return Boolean(userStore.selectedAddress);
    });

    const showGiftProgress = computed(() => {
        const intent = checkoutIntent?.value ?? checkoutIntent;
        if (!intent || typeof intent !== "object") {
            return false;
        }

        return Boolean(intent.serverDelivery?.method);
    });

    return {
        isDeliveryDataFilled,
        showGiftProgress,
    };
}
