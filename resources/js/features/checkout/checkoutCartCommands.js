import { updateCheckoutCartRequest } from "../../api/checkoutApi";

export async function updateCheckoutCartLine(store, productId, quantity, payload = null) {
    await store.ensureDraftCheckout();
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
