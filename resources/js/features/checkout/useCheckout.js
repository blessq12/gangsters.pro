import { computed, ref } from "vue";
import { useCheckoutStore } from "../../stores/checkoutStore";
import { useOrderStore } from "../../stores/orderStore";
import { useUserStore } from "../../stores/userStore";
import { useUiStore } from "../../stores/uiStore";
import { useCheckoutSession } from "./useCheckoutSession";
import { useCheckoutDeliveryStep } from "./useCheckoutDeliveryStep";
import { useCheckoutGuestStep } from "./useCheckoutGuestStep";
import { useCheckoutPaymentStep } from "./useCheckoutPaymentStep";
import { useCheckoutWizard } from "./useCheckoutWizard";

export function useCheckout() {
    const checkoutIntent = useCheckoutStore();
    const orderStore = useOrderStore();
    const userStore = useUserStore();
    const uiStore = useUiStore();
    const isGuestCheckout = ref(false);
    const isAuthenticated = computed(
        () => Boolean(userStore.token) && Boolean(userStore.profile?.id),
    );

    const cartView = useCheckoutSession();
    const guestStep = useCheckoutGuestStep(checkoutIntent);
    const paymentStep = useCheckoutPaymentStep(checkoutIntent);
    const deliveryStep = useCheckoutDeliveryStep({
        checkoutIntent,
        userStore,
        isGuestCheckout,
        isAuthenticated,
    });
    const wizard = useCheckoutWizard({
        checkoutIntent,
        orderStore,
        userStore,
        uiStore,
        isAuthenticated,
        cartView,
        guestStep,
        deliveryStep,
        paymentStep,
        isGuestCheckout,
    });

    return {
        cartStore: checkoutIntent,
        checkoutIntent,
        checkoutStore: checkoutIntent,
        orderStore,
        userStore,
        ...cartView,
        ...wizard,
        ...deliveryStep,
        ...guestStep,
        ...paymentStep,
    };
}
