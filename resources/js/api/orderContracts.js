/**
 * DTO‑builder для создания заказа.
 *
 * @param {Object} params
 * @param {{ id: number|null }|null} params.client
 * @param {Array<{ productId: number, qty: number }>} params.cartItems
 * @param {{ method: string|null, address: any, comment: string|null }} params.deliveryInfo
 * @param {{ method: string|null }} params.paymentInfo
 * @param {Object|null} params.selectedAddress
 * @param {string|null} params.customerComment
 * @returns {{
 *   client_id: number|null,
 *   items: Array<{ product_id: number, quantity: number }>,
 *   delivery_method: string,
 *   delivery_address: any,
 *   delivery_comment: string|null,
 *   payment_method: string
 * }}
 */
export function buildCreateOrderPayloadDto({
    client,
    cartItems,
    deliveryInfo,
    paymentInfo,
    selectedAddress,
    customerComment,
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

    return {
        client_id: client?.id ?? null,
        items,
        delivery_method: deliveryInfo?.method ?? "courier",
        delivery_address: deliveryAddress,
        delivery_comment:
            deliveryInfo?.comment || customerComment || null,
        payment_method: paymentInfo?.method ?? "card",
    };
}

