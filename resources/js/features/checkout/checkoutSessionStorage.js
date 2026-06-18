export const CHECKOUT_SESSION_KEY = "gangsters_order_draft_v1";

export const CHECKOUT_WIZARD_STEPS = ["cart", "guest", "delivery", "payment", "confirm"];

export function readCheckoutSessionPayload() {
    if (typeof window === "undefined") {
        return null;
    }

    try {
        const raw = window.sessionStorage.getItem(CHECKOUT_SESSION_KEY);
        if (!raw) {
            return null;
        }
        const parsed = JSON.parse(raw);
        return parsed && typeof parsed === "object" ? parsed : null;
    } catch {
        return null;
    }
}

export function writeCheckoutSessionPayload(payload) {
    if (typeof window === "undefined") {
        return;
    }

    window.sessionStorage.setItem(CHECKOUT_SESSION_KEY, JSON.stringify(payload));
}

export function clearCheckoutSessionPayload() {
    if (typeof window === "undefined") {
        return;
    }

    window.sessionStorage.removeItem(CHECKOUT_SESSION_KEY);
}

export function buildCheckoutSessionSnapshot(store) {
    const deliveryInfo = store.deliveryInfo;
    let deliveryInfoForSession = deliveryInfo;

    if (
        deliveryInfo?.address &&
        typeof deliveryInfo.address === "object"
    ) {
        const { latitude, longitude, ...addressWithoutCoords } =
            deliveryInfo.address;
        deliveryInfoForSession = {
            ...deliveryInfo,
            address: addressWithoutCoords,
        };
    }

    return {
        clientRequestId: store.clientRequestId,
        localCart: store.cartItems.filter((item) => !item.isSystem),
        forms: {
            deliveryInfo: deliveryInfoForSession,
            paymentInfo: store.paymentInfo,
            guestContact: store.guestContact,
            customerComment: store.customerComment,
            promotions: store.promotions,
        },
    };
}
