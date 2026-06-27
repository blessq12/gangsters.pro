export const DOCK_DISMISS_KIND = Object.freeze({
    IMMEDIATE: "immediate",
    CONFIRM_CHECKOUT: "confirm_checkout",
    CONFIRM_ORDER: "confirm_order",
});

const CHECKOUT_EXIT_CONFIRM = Object.freeze({
    kind: DOCK_DISMISS_KIND.CONFIRM_CHECKOUT,
    title: "Выйти из оформления?",
    message: "Введённые данные сохранятся. Продолжить покупки?",
    confirmLabel: "Продолжить покупки",
    cancelLabel: "Остаться",
});

const ORDER_CANCEL_CONFIRM = Object.freeze({
    kind: DOCK_DISMISS_KIND.CONFIRM_ORDER,
    title: "Отменить оформление?",
    message: "Заказ ещё не отправлен. Вернуться к покупкам?",
    confirmLabel: "В каталог",
    cancelLabel: "Остаться",
});

function hasText(value) {
    return String(value ?? "").trim() !== "";
}

function isGuestStepDirty(checkoutStore) {
    const guest = checkoutStore?.guestContact;
    if (!guest || typeof guest !== "object") {
        return false;
    }

    return (
        hasText(guest.name)
        || hasText(guest.phone)
        || hasText(guest.email)
    );
}

function isDeliveryStepDirty(checkoutStore) {
    const delivery = checkoutStore?.deliveryInfo;
    if (!delivery || typeof delivery !== "object") {
        return false;
    }

    if (hasText(delivery.comment)) {
        return true;
    }

    const address = delivery.address;
    if (!address || typeof address !== "object") {
        return false;
    }

    return (
        hasText(address.street)
        || hasText(address.house)
        || hasText(address.entrance)
        || hasText(address.apartment)
    );
}

function isPaymentStepDirty(checkoutStore) {
    const changeFrom = checkoutStore?.paymentInfo?.changeFrom;
    return changeFrom != null && String(changeFrom).trim() !== "";
}

function isFulfillmentStepDirty(checkoutStore) {
    return (
        isDeliveryStepDirty(checkoutStore)
        || isPaymentStepDirty(checkoutStore)
        || Boolean(checkoutStore?.paymentInfo?.method)
    );
}

/**
 * @param {{
 *   dockActiveId: string | null,
 *   checkoutWizardStep: string,
 *   checkoutStore: object,
 * }} context
 */
export function resolveDockDismissPolicy({
    dockActiveId,
    checkoutWizardStep,
    checkoutStore,
}) {
    if (!dockActiveId) {
        return { kind: DOCK_DISMISS_KIND.IMMEDIATE };
    }

    if (dockActiveId !== "cart") {
        return { kind: DOCK_DISMISS_KIND.IMMEDIATE };
    }

    const step = checkoutWizardStep || "cart";

    if (step === "cart" || step === "success") {
        return { kind: DOCK_DISMISS_KIND.IMMEDIATE };
    }

    if (step === "confirm") {
        return { ...ORDER_CANCEL_CONFIRM };
    }

    if (step === "guest") {
        return isGuestStepDirty(checkoutStore)
            ? { ...CHECKOUT_EXIT_CONFIRM }
            : { kind: DOCK_DISMISS_KIND.IMMEDIATE };
    }

    if (step === "fulfillment") {
        return isFulfillmentStepDirty(checkoutStore)
            ? { ...CHECKOUT_EXIT_CONFIRM }
            : { kind: DOCK_DISMISS_KIND.IMMEDIATE };
    }

    return { kind: DOCK_DISMISS_KIND.IMMEDIATE };
}
