import { ref } from "vue";
import { useCheckoutStore } from "../../stores/checkoutStore";
import { useOrderStore } from "../../stores/orderStore";
import { useUserStore } from "../../stores/userStore";
import { useUiStore } from "../../stores/uiStore";
import { useCartCommands } from "../shoppingSession/useCartCommands";
import { useClientReadModel } from "../client/useClient";
import { useCheckoutCartView } from "./useCheckoutCartView";
import { useCheckoutDeliveryStep } from "./useCheckoutDeliveryStep";
import { useCheckoutGuestStep } from "./useCheckoutGuestStep";
import { useCheckoutPaymentStep } from "./useCheckoutPaymentStep";
import { useCheckoutWizard } from "./useCheckoutWizard";

export function useCheckout() {
    const cartCommands = useCartCommands();
    const checkoutIntent = useCheckoutStore();
    const orderStore = useOrderStore();
    const userStore = useUserStore();
    const uiStore = useUiStore();
    const clientReadModel = useClientReadModel();
    const isGuestCheckout = ref(false);

    const cartView = useCheckoutCartView();
    const guestStep = useCheckoutGuestStep(checkoutIntent);
    const paymentStep = useCheckoutPaymentStep(checkoutIntent);
    const deliveryStep = useCheckoutDeliveryStep({
        checkoutIntent,
        userStore,
        isGuestCheckout,
        isAuthenticated: clientReadModel.isAuthenticated,
    });
    const wizard = useCheckoutWizard({
        checkoutIntent,
        orderStore,
        userStore,
        uiStore,
        clientReadModel,
        cartView,
        guestStep,
        deliveryStep,
        paymentStep,
        isGuestCheckout,
    });

    return {
        cartStore: cartCommands.cartStore,
        checkoutIntent,
        checkoutStore: checkoutIntent,
        orderStore,
        userStore,
        clientReadModel,
        ...cartView,
        ...wizard,
        ...deliveryStep,
        ...guestStep,
        ...paymentStep,
    };
}
