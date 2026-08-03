import {
    placeOrderRequest,
    quoteOrderRequest,
} from "../../api/orderApi";
import { useUserStore } from "../../stores/userStore";
import { adaptQuoteToCheckoutSnapshot } from "../../domain/order/adaptQuoteToCheckoutSnapshot";
import {
    buildClientPayload,
    buildDeliveryPayload,
    buildPaymentPayload,
} from "../../domain/order/checkoutServerMappers";
import {
    normalizeCheckoutSessionForms,
    readCheckoutSessionPayload,
    writeCheckoutSessionPayload,
} from "./checkoutSessionStorage";
import { isComplementCartLine } from "../../domain/order/normalizeCheckoutCart";
import { roundRubles2 } from "../../utils/moneyFormat";

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

/**
 * Адрес профиля для preview/place: auth + courier + выбранный адрес.
 * Гость использует deliveryInfo.address из store.
 */
export function resolveCheckoutPreviewAddress(store) {
    if (store.deliveryInfo?.method !== "courier") {
        return null;
    }

    const isGuest = Boolean(
        store.guestContact?.name && store.guestContact?.phone,
    );
    if (isGuest) {
        return null;
    }

    const userStore = useUserStore();
    if (!userStore.token || userStore.selectedAddressId == null) {
        return null;
    }

    return userStore.selectedAddress ?? null;
}

function effectivePreviewAddress(store, selectedAddress) {
    if (selectedAddress != null) {
        return selectedAddress;
    }

    return resolveCheckoutPreviewAddress(store);
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

function resolveComplementProductIds(store) {
    return store.cartItems
        .filter((item) => isComplementCartLine(item))
        .map((item) => Number(item.productId) || 0)
        .filter((id) => id > 0);
}

function resolveCoords(selectedAddress, store) {
    const source =
        selectedAddress
        ?? (store.deliveryInfo?.address && typeof store.deliveryInfo.address === "object"
            ? store.deliveryInfo.address
            : null);

    if (!source || typeof source !== "object") {
        return { latitude: null, longitude: null };
    }

    const latitude =
        source.latitude != null ? Number(source.latitude) : null;
    const longitude =
        source.longitude != null ? Number(source.longitude) : null;

    return {
        latitude: Number.isFinite(latitude) ? latitude : null,
        longitude: Number.isFinite(longitude) ? longitude : null,
    };
}

export function buildQuoteOrderPayload(store, selectedAddress = null, options = {}) {
    const userLines = store.userItems.map((item) => ({
        product_id: item.productId,
        quantity: item.qty,
    }));

    const clientId = resolveRegisteredClientId(store, options);
    const isGuest = Boolean(
        options.isGuest
        || (store.guestContact?.name && store.guestContact?.phone),
    );

    const clientPayload = buildClientPayload(store, {
        clientId,
        isGuest: isGuest || clientId == null,
    });

    const deliveryPayload = store.deliveryInfo.method
        ? buildDeliveryPayload(store, selectedAddress)
        : null;

    const paymentPayload = store.paymentInfo.method
        ? buildPaymentPayload(store)
        : null;

    const coords = resolveCoords(selectedAddress, store);
    const giftProductId = store.promotions.freeRollGiftProductId;

    return {
        lines: userLines,
        delivery_method: deliveryPayload?.method ?? store.deliveryInfo.method ?? "courier",
        client: clientPayload,
        address: deliveryPayload?.address ?? null,
        delivery_comment: deliveryPayload?.comment,
        scheduled_at: deliveryPayload?.scheduled_at,
        payment_method: paymentPayload?.method ?? "cash",
        change_from_rubles: paymentPayload?.change_from_rubles,
        gift_product_id: giftProductId != null ? Number(giftProductId) : null,
        complement_product_ids: resolveComplementProductIds(store),
        latitude: coords.latitude,
        longitude: coords.longitude,
    };
}

/** @deprecated используй buildQuoteOrderPayload */
export function buildOrderDraftPayload(store, selectedAddress = null, options = {}) {
    return buildQuoteOrderPayload(store, selectedAddress, options);
}

export async function refreshOrderDraftPreview(store, selectedAddress = null, options = {}) {
    ensureCheckoutSessionActive(store);

    const requestSeq = ++store.previewRequestSeq;
    store.flushing = true;
    store.error = null;
    const previewAddress = effectivePreviewAddress(store, selectedAddress);

    try {
        const quote = await quoteOrderRequest(
            buildQuoteOrderPayload(store, previewAddress, options),
        );

        if (requestSeq !== store.previewRequestSeq) {
            return quote;
        }

        const snapshot = adaptQuoteToCheckoutSnapshot(quote);
        store.applyFromServer(snapshot);
        store.persistSession();
        return quote;
    } catch (e) {
        const status = e?.response?.status;
        store.error =
            e?.response?.data?.message || "Не удалось пересчитать оформление.";
        // 422 — ожидаемая валидация неполного черновика, не логируем и не валим UI.
        if (status === 422) {
            return null;
        }
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
    const previewAddress = effectivePreviewAddress(store, selectedAddress);

    try {
        const quote = await quoteOrderRequest(
            buildQuoteOrderPayload(store, previewAddress),
        );

        const body = {
            client_request_id: store.clientRequestId || resolveClientRequestId(),
            cart: quote.cart,
            client: quote.client,
            delivery: quote.delivery,
            payment: quote.payment,
        };

        const data = await placeOrderRequest(body);
        store.clearAfterCompleted();
        return { order: data };
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
