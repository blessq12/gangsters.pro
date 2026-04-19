import { migrateLocalShoppingRequest, fetchShoppingStateRequest, mergeShoppingSessionRequest } from "../../api/shoppingApi";
import { applyShoppingSnapshotToStores } from "./shoppingApplySnapshot";

const MIGRATION_FLAG_KEY = "gangsters_shopping_backend_migrated_v1";

function readJsonLocalStorage(key) {
    if (typeof window === "undefined") return null;
    try {
        const raw = window.localStorage.getItem(key);
        if (!raw) return null;
        return JSON.parse(raw);
    } catch {
        return null;
    }
}

/**
 * Собирает payload для POST /api/shopping/migrate из старых ключей localStorage.
 */
export function buildMigratePayloadFromLocalStorage() {
    const cartParsed = readJsonLocalStorage("gangsters_cart");
    const favParsed = readJsonLocalStorage("gangsters_favorites");
    const orderParsed = readJsonLocalStorage("gangsters_order_draft");

    const cart_items = [];
    if (cartParsed && Array.isArray(cartParsed.cartItems)) {
        for (const row of cartParsed.cartItems) {
            const productId = row?.productId ?? row?.productSnapshot?.id;
            const qty = row?.qty ?? row?.quantity;
            if (productId && qty) {
                cart_items.push({ productId: Number(productId), qty: Number(qty) });
            }
        }
    }

    const favorite_product_ids = [];
    if (favParsed && Array.isArray(favParsed.items)) {
        for (const row of favParsed.items) {
            const id = row?.productId ?? row?.productSnapshot?.id;
            if (id) {
                favorite_product_ids.push(Number(id));
            }
        }
    }

    let checkout_draft = null;
    if (orderParsed && typeof orderParsed === "object") {
        checkout_draft = {
            delivery_info: orderParsed.deliveryInfo
                ? {
                      method: orderParsed.deliveryInfo.method ?? null,
                      address: orderParsed.deliveryInfo.address ?? null,
                      comment: orderParsed.deliveryInfo.comment ?? null,
                      scheduled_at: orderParsed.deliveryInfo.scheduledAt ?? null,
                  }
                : undefined,
            payment_info: orderParsed.paymentInfo
                ? {
                      method: orderParsed.paymentInfo.method ?? null,
                      change_from: orderParsed.paymentInfo.changeFrom ?? null,
                  }
                : undefined,
            guest_contact: orderParsed.guestContact ?? undefined,
            customer_comment: orderParsed.customerComment ?? undefined,
        };
    }

    return { cart_items, favorite_product_ids, checkout_draft };
}

/**
 * Одноразовая миграция localStorage → бэкенд, затем загрузка state.
 */
export async function bootstrapShoppingFromApi() {
    if (typeof window === "undefined") return;

    try {
        if (!window.sessionStorage.getItem(MIGRATION_FLAG_KEY)) {
            const payload = buildMigratePayloadFromLocalStorage();
            const hasPayload =
                (payload.cart_items && payload.cart_items.length > 0) ||
                (payload.favorite_product_ids && payload.favorite_product_ids.length > 0) ||
                (payload.checkout_draft &&
                    typeof payload.checkout_draft === "object" &&
                    Object.keys(payload.checkout_draft).length > 0);

            if (hasPayload) {
                const migrated = await migrateLocalShoppingRequest(payload);
                applyShoppingSnapshotToStores(migrated);
            }

            window.sessionStorage.setItem(MIGRATION_FLAG_KEY, "1");
            window.localStorage.removeItem("gangsters_cart");
            window.localStorage.removeItem("gangsters_favorites");
            window.localStorage.removeItem("gangsters_order_draft");
        }

        const state = await fetchShoppingStateRequest();
        applyShoppingSnapshotToStores(state);
    } catch (e) {
        console.error("bootstrapShoppingFromApi failed", e);
    }
}

export async function mergeShoppingAfterAuth() {
    try {
        const data = await mergeShoppingSessionRequest();
        applyShoppingSnapshotToStores(data);
    } catch (e) {
        console.error("mergeShoppingAfterAuth failed", e);
    }
}
