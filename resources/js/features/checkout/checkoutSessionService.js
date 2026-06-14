import {
    confirmCheckoutRequest,
    createCheckoutRequest,
    fetchCheckoutRequest,
    setCheckoutClientRequest,
    setCheckoutDeliveryRequest,
    setCheckoutPaymentRequest,
    updateCheckoutCartRequest,
} from "../../api/checkoutApi";
import {
    isAxiosNetworkError,
    isAxiosNotFound,
} from "../../utils/api/mapApiError";
import {
    buildClientPayload,
    buildDeliveryPayload,
    buildPaymentPayload,
} from "./checkoutServerMappers";
import {
    clearCheckoutSessionPayload,
    readCheckoutSessionPayload,
    writeCheckoutSessionPayload,
} from "./checkoutSessionStorage";

export async function ensureDraftCheckout(store) {
    if (store.hasCheckout) {
        return store.checkoutId;
    }

    const created = await createCheckoutRequest();
    store.applyFromServer(created);
    store.sessionReady = true;

    return store.checkoutId;
}

export async function tryRestoreCheckoutSession(store) {
    const saved = readCheckoutSessionPayload();
    if (!saved?.checkoutId || saved.status !== "draft") {
        return false;
    }

    try {
        const remote = await fetchCheckoutRequest(saved.checkoutId);
        if (remote?.status !== "draft") {
            clearCheckoutSessionPayload();
            return false;
        }

        store.applyFromServer(remote);
        store.sessionReady = true;

        return true;
    } catch (error) {
        if (isAxiosNotFound(error)) {
            clearCheckoutSessionPayload();
            return false;
        }

        if (!isAxiosNetworkError(error)) {
            clearCheckoutSessionPayload();
            return false;
        }

        if (!saved.snapshot) {
            return false;
        }

        store.checkoutId = saved.checkoutId;
        store.status = saved.status;
        store.applyFromServer(
            saved.snapshot ?? { checkout_id: saved.checkoutId, status: "draft" },
        );
        store.sessionReady = true;

        return true;
    }
}

export async function bootstrapCheckoutSession(store) {
    if (store.sessionReady) {
        return;
    }

    if (await tryRestoreCheckoutSession(store)) {
        return;
    }

    await ensureDraftCheckout(store);
}

export async function updateCheckoutCartLine(store, productId, quantity, payload = null) {
    await ensureDraftCheckout(store);
    store.loading = true;
    store.error = null;

    try {
        const body = {
            product_id: Number(productId),
            quantity: Number(quantity),
        };
        if (payload != null) {
            body.payload = payload;
        }

        const data = await updateCheckoutCartRequest(store.checkoutId, body);
        store.applyFromServer(data);
        return data;
    } catch (e) {
        console.error("updateCartLine / checkout", e);
        store.error =
            e?.response?.data?.message || "Не удалось обновить корзину.";
        throw e;
    } finally {
        store.loading = false;
    }
}

export async function setCheckoutPromotionGift(store, productId) {
    const previousId = store.promotions.freeRollGiftProductId;
    const nextId = productId != null ? Number(productId) || null : null;

    if (previousId != null && previousId !== nextId) {
        await updateCheckoutCartLine(store, previousId, 0, { kind: "gift" });
    }

    if (nextId != null) {
        await updateCheckoutCartLine(store, nextId, 1, { kind: "gift" });
    }

    store.patchLocal({
        promotions: {
            freeRollGiftProductId: nextId,
        },
    });
}

export async function flushClientToServer(store, { clientId = null, isGuest = false } = {}) {
    await ensureDraftCheckout(store);
    store.flushing = true;

    try {
        const data = await setCheckoutClientRequest(
            store.checkoutId,
            buildClientPayload(store, { clientId, isGuest }),
        );
        store.applyFromServer(data);
    } catch (e) {
        console.error("flushClientToServer / checkout", e);
        throw e;
    } finally {
        store.flushing = false;
    }
}

export async function flushDeliveryToServer(store, selectedAddress = null) {
    await ensureDraftCheckout(store);
    store.flushing = true;

    try {
        const data = await setCheckoutDeliveryRequest(
            store.checkoutId,
            buildDeliveryPayload(store, selectedAddress),
        );
        store.applyFromServer(data);
    } catch (e) {
        console.error("flushDeliveryToServer / checkout", e);
        throw e;
    } finally {
        store.flushing = false;
    }
}

export async function flushPaymentToServer(store) {
    await ensureDraftCheckout(store);
    store.flushing = true;

    try {
        const data = await setCheckoutPaymentRequest(
            store.checkoutId,
            buildPaymentPayload(store),
        );
        store.applyFromServer(data);
    } catch (e) {
        console.error("flushPaymentToServer / checkout", e);
        throw e;
    } finally {
        store.flushing = false;
    }
}

export async function flushCheckoutToServer(store, options = {}) {
    const { clientId = null, isGuest = false, selectedAddress = null } = options;

    if (isGuest || clientId != null) {
        await flushClientToServer(store, { clientId, isGuest });
    }
    if (store.deliveryInfo.method) {
        await flushDeliveryToServer(store, selectedAddress);
    }
    if (store.paymentInfo.method) {
        await flushPaymentToServer(store);
    }
}

export async function confirmCheckoutOnServer(store) {
    await ensureDraftCheckout(store);
    store.flushing = true;

    try {
        const data = await confirmCheckoutRequest(store.checkoutId);
        store.applyFromServer(data);
        clearCheckoutSessionPayload();
        return data;
    } catch (e) {
        console.error("confirmCheckout", e);
        store.error =
            e?.response?.data?.message || "Не удалось подтвердить оформление.";
        throw e;
    } finally {
        store.flushing = false;
    }
}

export function persistCheckoutSession(payload) {
    writeCheckoutSessionPayload(payload);
}
