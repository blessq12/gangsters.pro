import { ref } from "vue";
import { validateRuPhoneForSubmit } from "../../validation/ruPhone";

export function isGuestContactComplete(guestContact) {
    const name = String(guestContact?.name || "").trim();
    const phone = String(guestContact?.phone || "").trim();

    return name !== "" && phone !== "";
}

export function useCheckoutGuestStep(checkoutIntent) {
    const guestStepError = ref("");

    function getGuestStepError() {
        const guestContact = checkoutIntent.guestContact;
        if (!String(guestContact?.name || "").trim()) {
            return "Укажи имя для связи.";
        }
        const phoneCheck = validateRuPhoneForSubmit(guestContact?.phone);
        if (!phoneCheck.ok) {
            return phoneCheck.message;
        }

        return "";
    }

    function validateGuestStep() {
        const message = getGuestStepError();
        guestStepError.value = message;

        return message === "";
    }

    function setGuestContact(payload) {
        checkoutIntent.setGuestContact(payload);
    }

    return {
        guestStepError,
        getGuestStepError,
        validateGuestStep,
        setGuestContact,
    };
}
