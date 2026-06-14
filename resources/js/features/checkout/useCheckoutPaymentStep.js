import { ref } from "vue";
import {
    isCheckoutPaymentMethod,
    normalizeCheckoutPaymentMethod,
} from "./checkoutPaymentMethods";

export function useCheckoutPaymentStep(checkoutIntent) {
    const paymentStepError = ref("");

    function getPaymentStepError() {
        if (!checkoutIntent.paymentInfo.method) {
            return "Выбери способ оплаты.";
        }

        return "";
    }

    function validatePaymentStep() {
        const message = getPaymentStepError();
        paymentStepError.value = message;

        return message === "";
    }

    function ensurePaymentDefaults() {
        if (!isCheckoutPaymentMethod(checkoutIntent.paymentInfo.method)) {
            checkoutIntent.setPaymentInfo({
                method: normalizeCheckoutPaymentMethod(
                    checkoutIntent.paymentInfo.method,
                ),
            });
        }
    }

    function setPaymentMethod(method) {
        checkoutIntent.setPaymentInfo({
            method: normalizeCheckoutPaymentMethod(method),
        });
    }

    function setPaymentChangeFrom(changeFrom) {
        checkoutIntent.setPaymentInfo({ changeFrom });
    }

    function setCustomerComment(comment) {
        checkoutIntent.setCustomerComment(comment);
    }

    return {
        paymentStepError,
        getPaymentStepError,
        validatePaymentStep,
        ensurePaymentDefaults,
        setPaymentMethod,
        setPaymentChangeFrom,
        setCustomerComment,
    };
}
