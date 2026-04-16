import { validateRuPhoneForSubmit } from "../../validation/ruPhone";

export function useCheckoutValidation(checkoutState) {
    const {
        orderStore,
        isGuestCheckout,
        deliveryStepError,
        paymentStepError,
    } = checkoutState;

    function getDeliveryStepError(selectedAddress) {
        if (!orderStore.deliveryInfo.method) {
            return "Выбери способ доставки.";
        }

        if (isGuestCheckout.value) {
            const gc = orderStore.guestContact;
            if (!String(gc?.name || "").trim()) {
                return "Укажи имя для связи.";
            }
            const phoneCheck = validateRuPhoneForSubmit(gc?.phone);
            if (!phoneCheck.ok) {
                return phoneCheck.message;
            }
            if (orderStore.deliveryInfo.method === "courier") {
                const a = orderStore.deliveryInfo.address;
                if (!String(a?.street || "").trim() || !String(a?.house || "").trim()) {
                    return "Укажи улицу и дом для курьера.";
                }
            }
        } else if (
            orderStore.deliveryInfo.method === "courier" &&
            !selectedAddress
        ) {
            return "Выбери адрес доставки или добавь новый.";
        }

        return "";
    }

    function validateDeliveryStep(selectedAddress) {
        const message = getDeliveryStepError(selectedAddress);
        deliveryStepError.value = message;

        return message === "";
    }

    function getPaymentStepError() {
        if (!orderStore.paymentInfo.method) {
            return "Выбери способ оплаты.";
        }

        return "";
    }

    function validatePaymentStep() {
        const message = getPaymentStepError();
        paymentStepError.value = message;

        return message === "";
    }

    function canConfirmOrder(selectedAddress, hasCartItems) {
        if (!hasCartItems.value) return false;

        return (
            getDeliveryStepError(selectedAddress) === "" &&
            getPaymentStepError() === ""
        );
    }

    return {
        validateDeliveryStep,
        validatePaymentStep,
        canConfirmOrder,
    };
}

