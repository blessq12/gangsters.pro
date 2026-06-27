import {
    isCheckoutPaymentMethod,
    normalizeCheckoutPaymentMethod,
} from "./checkoutPaymentMethods";
import { useFormFieldErrors } from "../../composables/forms/useFormFieldErrors";

export function useCheckoutPaymentStep(checkoutIntent) {
    const paymentFieldErrors = useFormFieldErrors();

    function validatePaymentStep() {
        paymentFieldErrors.clearAll();

        if (!checkoutIntent.paymentInfo.method) {
            paymentFieldErrors.setFieldError("method", "Выбери способ оплаты.");
        }

        return !paymentFieldErrors.hasAny.value;
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
        paymentFieldErrors.clearField("method");
    }

    function setPaymentChangeFrom(changeFrom) {
        checkoutIntent.setPaymentInfo({ changeFrom });
    }

    return {
        paymentFieldErrors,
        validatePaymentStep,
        ensurePaymentDefaults,
        setPaymentMethod,
        setPaymentChangeFrom,
    };
}
