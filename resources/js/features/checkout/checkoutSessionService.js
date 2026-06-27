import {
    placeOrderRequest,
    previewOrderDraftRequest,
} from "../../api/orderDraftApi";
import { useUserStore } from "../../stores/userStore";
import {
    buildClientPayload,
    buildDeliveryPayload,
    buildPaymentPayload,
} from "./checkoutServerMappers";
import {
    clearCheckoutSessionPayload,
    normalizeCheckoutSessionForms,
    readCheckoutSessionPayload,
    writeCheckoutSessionPayload,
} from "./checkoutSessionStorage";
import { roundRubles2 } from "../../utils/moneyFormat";

const CLIENT_REQUEST_KEY = "client_request_id";

export function resolveClientRequestId() {
    if (typeof window === "undefined") {
        return crypto.randomUUID();
    }

    const saved = readCheckoutSessionPayload();
    if (saved?.clientRequestId) {
        return saved.clientRequestId;
    }

    const id = crypto.randomUUID();
    writeCheckoutSessionPayload({ clientRequestId: id });

    return id;
}

function ensureCheckoutSessionActive(store) {
    if (store.sessionReady) {
        return;
    }

    store.clientRequestId = resolveClientRequestId();
    store.sessionReady = true;
}

function resolveRegisteredClientId(store, options = {}) {
    if (options.clientId != null) {
        return Number(options.clientId);
    }

    if (store.serverClient?.client_id != null) {
        return Number(store.serverClient.client_id);
    }

    const userStore = useUserStore();
    if (userStore.token && userStore.profile?.id != null) {
        return Number(userStore.profile.id);
    }

    return null;
}

export function buildOrderDraftPayload(store, selectedAddress = null, options = {}) {
    const userLines = store.userItems.map((item) => ({
        product_id: item.productId,
        quantity: item.qty,
        payload: item.payload ?? null,
    }));

    const clientPayload = buildClientPayload(store, {
        clientId: resolveRegisteredClientId(store, options),
        isGuest: Boolean(store.guestContact?.name && store.guestContact?.phone),
    });

    const deliveryPayload = store.deliveryInfo.method
        ? buildDeliveryPayload(store, selectedAddress)
        : null;

    const paymentPayload = store.paymentInfo.method
        ? buildPaymentPayload(store)
        : null;

    return {
        cart: {
            lines: userLines,
            selected_gift_product_id: store.promotions.freeRollGiftProductId,
        },
        client: clientPayload.client_id != null || clientPayload.name ? clientPayload : null,
        delivery: deliveryPayload,
        payment: paymentPayload,
    };
}

export async function refreshOrderDraftPreview(store, selectedAddress = null, options = {}) {
    ensureCheckoutSessionActive(store);

    const requestSeq = ++store.previewRequestSeq;
    store.flushing = true;
    store.error = null;

    try {
        const data = await previewOrderDraftRequest(
            buildOrderDraftPayload(store, selectedAddress, options),
        );

        if (requestSeq !== store.previewRequestSeq) {
            return data;
        }

        store.applyFromServer(data);
        store.persistSession();
        return data;
    } catch (e) {
        console.error("refreshOrderDraftPreview", e);
        store.error =
            e?.response?.data?.message || "Не удалось пересчитать оформление.";
        throw e;
    } finally {
        store.flushing = false;
    }
}

export async function bootstrapCheckoutSession(store) {
    if (store.sessionReady) {
        return;
    }

    const saved = readCheckoutSessionPayload();
    if (saved?.localCart?.length) {
        store.restoreLocalCart(saved.localCart);
    }
    if (saved?.forms) {
        store.patchLocal(normalizeCheckoutSessionForms(saved.forms));
    }

    store.clientRequestId = saved?.clientRequestId ?? resolveClientRequestId();
    store.sessionReady = true;

    if (store.hasCartItems) {
        try {
            await refreshOrderDraftPreview(store);
        } catch {
            // preview optional on bootstrap
        }
    }
}

export function buildLocalCartItem(product, qty, payload = null) {
    const productId = Number(product?.id);
    const quantity = Math.max(1, Number(qty) || 1);
    const unitRub = roundRubles2(Number(product?.price?.amount ?? product?.price) || 0);
    const unitKopecks = Math.round(unitRub * 100);
    const lineKopecks = unitKopecks * quantity;

    return {
        lineKey: `user:${productId}`,
        origin: "user",
        isSystem: false,
        lineKind: "user",
        productId,
        qty: quantity,
        productSnapshot: {
            id: productId,
            name: String(product?.name || ""),
            price: unitRub,
        },
        pricing: {
            listUnitPriceKopecks: unitKopecks,
            finalUnitPriceKopecks: unitKopecks,
            lineTotalKopecks: lineKopecks,
        },
        payload,
    };
}

export function recalculateLocalCartTotals(store) {
    const userLines = store.userItems;
    const subtotalKopecks = userLines.reduce(
        (sum, item) => sum + (Number(item.pricing?.lineTotalKopecks) || 0),
        0,
    );
    store.itemsSubtotalRubles = roundRubles2(subtotalKopecks / 100);
    store.itemsTotalRubles = store.itemsSubtotalRubles;
}

export function upsertLocalCartLine(store, product, quantity, payload = null) {
    const productId = Number(product?.id);
    const nextQty = Math.max(0, Number(quantity) || 0);
    const without = store.cartItems.filter(
        (item) => !(item.productId === productId && !item.isSystem),
    );

    if (nextQty > 0) {
        without.push(buildLocalCartItem(product, nextQty, payload));
    }

    store.cartItems = without;
    recalculateLocalCartTotals(store);
    store.persistSession();
}

export async function setCheckoutPromotionGift(store, productId) {
    store.patchLocal({
        promotions: {
            freeRollGiftProductId: productId != null ? Number(productId) || null : null,
        },
    });
    store.persistSession();

    if (store.hasCartItems) {
        await refreshOrderDraftPreview(store);
    }
}

export async function flushClientToServer(store, options = {}) {
    if (store.hasCartItems) {
        await refreshOrderDraftPreview(store, null, options);
    }
}

export async function flushDeliveryToServer(store, selectedAddress = null) {
    await refreshOrderDraftPreview(store, selectedAddress);
}

export async function flushPaymentToServer(store) {
    if (store.hasCartItems) {
        await refreshOrderDraftPreview(store);
    }
}

export async function flushCheckoutToServer(store, options = {}) {
    const { selectedAddress = null } = options;
    await refreshOrderDraftPreview(store, selectedAddress);
}

export async function placeOrderOnServer(store, selectedAddress = null) {
    store.previewRequestSeq += 1;
    store.flushing = true;
    store.error = null;

    try {
        const body = {
            client_request_id: store.clientRequestId || resolveClientRequestId(),
            ...buildOrderDraftPayload(store, selectedAddress),
        };

        const data = await placeOrderRequest(body);
        clearCheckoutSessionPayload();
        return data;
    } catch (e) {
        console.error("placeOrderOnServer", e);
        store.error =
            e?.response?.data?.message || "Не удалось оформить заказ.";
        throw e;
    } finally {
        store.flushing = false;
    }
}

export function persistCheckoutSession(payload) {
    writeCheckoutSessionPayload(payload);
}
