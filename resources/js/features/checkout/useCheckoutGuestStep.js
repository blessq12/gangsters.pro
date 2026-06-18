import { validateRuPhoneForSubmit } from "../../validation/ruPhone";
import { useFormFieldErrors } from "../../composables/forms/useFormFieldErrors";

export function isGuestContactComplete(guestContact) {
    const name = String(guestContact?.name || "").trim();
    const phone = String(guestContact?.phone || "").trim();

    return name !== "" && phone !== "";
}

export function useCheckoutGuestStep(checkoutIntent) {
    const guestFieldErrors = useFormFieldErrors();

    function validateGuestStep() {
        guestFieldErrors.clearAll();

        const guestContact = checkoutIntent.guestContact;
        if (!String(guestContact?.name || "").trim()) {
            guestFieldErrors.setFieldError("name", "Укажи имя для связи.");
        }

        const phoneCheck = validateRuPhoneForSubmit(guestContact?.phone);
        if (!phoneCheck.ok) {
            guestFieldErrors.setFieldError("phone", phoneCheck.message);
        }

        return !guestFieldErrors.hasAny.value;
    }

    function setGuestContact(payload) {
        checkoutIntent.setGuestContact(payload);
        if (payload?.name != null) {
            guestFieldErrors.clearField("name");
        }
        if (payload?.phone != null) {
            guestFieldErrors.clearField("phone");
        }
    }

    return {
        guestFieldErrors,
        validateGuestStep,
        setGuestContact,
    };
}
