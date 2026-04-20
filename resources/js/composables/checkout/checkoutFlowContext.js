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

    const {
        cartStore,
        userStore,
        activeStep,
        authTab,
        isGuestCheckout,
        newAddressForm,
        newAddressLoading,
        newAddressError,
        isNewAddressOpen,
        deliveryStepError,
        paymentStepError,
        cartItems,
        userCartItems,
        systemCartItems,
        totalAmount,
        userTotalAmount,
        systemTotalAmount,
        promoState,
        formatPrice,
        formatPhone,
        isAuthenticated,
        hasCartItems,
        orderStore,
        handleStartCheckout,
        handleContinueAsGuest,
        handleAuthCompleted,
        goToCart,
        goToDelivery,
        goToPayment,
        goToConfirm,
        goToSuccess,
        setDeliveryMethod,
        setDeliveryComment,
        setGuestContact,
        patchDeliveryAddress,
        selectAddress,
        setPaymentMethod,
        setPaymentChangeFrom,
        setCustomerComment,
        handleConfirmOrder,
        handleCreateAddress,
    } = flow;

    const checkoutState = {
        orderStore,
        cartItems,
        userCartItems,
        systemCartItems,
        totalAmount,
        userTotalAmount,
        systemTotalAmount,
        promoState,
        formatPrice,
        formatPhone,
        isAuthenticated,
        hasCartItems,
        activeStep,
        authTab,
        isGuestCheckout,
        newAddressForm,
        newAddressLoading,
        newAddressError,
        isNewAddressOpen,
        deliveryStepError,
        paymentStepError,
    };

    return {
        cartStore,
        userStore,
        checkoutState,
        handleStartCheckout,
        handleContinueAsGuest,
        handleAuthCompleted,
        goToCart,
        goToDelivery,
        goToPayment,
        goToConfirm,
        goToSuccess,
        setDeliveryMethod,
        setDeliveryComment,
        setGuestContact,
        patchDeliveryAddress,
        selectAddress,
        setPaymentMethod,
        setPaymentChangeFrom,
        setCustomerComment,
        handleConfirmOrder,
        handleCreateAddress,
    };
}
