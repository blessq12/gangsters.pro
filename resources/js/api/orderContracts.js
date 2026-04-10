/**
 * DTO‑builder для создания заказа.
 *
 * @param {Object} params
 * @param {Array<{ productId: number, qty: number }>} params.cartItems
 * @param {{ method: string|null, address: any, comment: string|null }} params.deliveryInfo
 * @param {{ method: string|null }} params.paymentInfo
 * @param {Object|null} params.selectedAddress
 * @param {string|null} params.customerComment
 * @param {{ name: string, phone: string, email?: string|null }|null} params.guestContact — только без авторизации
 * @returns {{
 *   items: Array<{ product_id: number, quantity: number }>,
 *   delivery_method: string,
 *   delivery_address: any,
 *   delivery_comment: string|null,
 *   payment_method: string,
 *   customer_name?: string,
 *   customer_phone?: string,
 *   customer_email?: string|null
 * }}
 */
export function buildCreateOrderPayloadDto({
    cartItems,
    deliveryInfo,
    paymentInfo,
    selectedAddress,
    customerComment,
    guestContact,
}) {
    const items = (cartItems || []).map((item) => ({
        product_id: Number(item.productId),
        quantity: Number(item.qty),
    }));

    let deliveryAddress = deliveryInfo?.address || null;

    if (selectedAddress && typeof selectedAddress === "object") {
        const entrance =
            selectedAddress.entrance ??
            selectedAddress.entrance_code ??
            deliveryAddress?.entrance ??
            null;

        deliveryAddress = {
            street: selectedAddress.street ?? deliveryAddress?.street ?? null,
            house: selectedAddress.house ?? deliveryAddress?.house ?? null,
            entrance,
            apartment:
                selectedAddress.apartment ??
                deliveryAddress?.apartment ??
                null,
        };
    }

    const payload = {
        items,
        delivery_method: deliveryInfo?.method ?? "courier",
        delivery_address: deliveryAddress,
        delivery_comment:
            deliveryInfo?.comment || customerComment || null,
        payment_method: paymentInfo?.method ?? "card",
    };

    if (
        guestContact &&
        String(guestContact.phone || "").trim() !== "" &&
        String(guestContact.name || "").trim() !== ""
    ) {
        payload.customer_name = String(guestContact.name).trim();
        payload.customer_phone = String(guestContact.phone).trim();
        const em = guestContact.email != null ? String(guestContact.email).trim() : "";
        payload.customer_email = em !== "" ? em : null;
    }

    return payload;
}

