import {
    fromServerCheckoutPaymentMethod,
    normalizeCheckoutPaymentMethod,
    toServerCheckoutPaymentMethod,
} from "./checkoutPaymentMethods";

export function mapClientToGuestContact(client) {
    if (!client || typeof client !== "object") {
        return { name: "", phone: "", email: "" };
    }

    return {
        name: typeof client.name === "string" ? client.name : "",
        phone: typeof client.phone === "string" ? client.phone : "",
        email: typeof client.email === "string" ? client.email : "",
    };
}

export function mapDeliveryToLocal(delivery) {
    if (!delivery || typeof delivery !== "object") {
        return {
            method: null,
            address: null,
            comment: "",
            scheduledAt: null,
        };
    }

    return {
        method: delivery.method ?? null,
        address: delivery.address ?? null,
        comment: delivery.comment ?? "",
        scheduledAt: delivery.scheduled_at ?? null,
    };
}

export function mergeCheckoutDeliveryComment(deliveryComment, customerComment) {
    const parts = [
        String(deliveryComment || "").trim(),
        String(customerComment || "").trim(),
    ].filter(Boolean);

    return parts.length > 0 ? parts.join("\n\n") : "";
}

export function mapPaymentToLocal(payment) {
    if (!payment || typeof payment !== "object") {
        return {
            method: null,
            changeFrom: null,
        };
    }

    return {
        method:
            payment.method != null
                ? fromServerCheckoutPaymentMethod(payment.method)
                : null,
        changeFrom: payment.change_from_rubles ?? null,
    };
}

export function buildClientPayload(store, { clientId = null, isGuest = false } = {}) {
    if (clientId != null) {
        return {
            client_id: Number(clientId),
            name: store.guestContact.name || undefined,
            phone: store.guestContact.phone || undefined,
            email: store.guestContact.email || undefined,
        };
    }

    if (!isGuest) {
        return {
            client_id: null,
        };
    }

    return {
        name: store.guestContact.name,
        phone: store.guestContact.phone,
        email: store.guestContact.email || undefined,
    };
}

export function buildDeliveryPayload(store, selectedAddress = null) {
    const method = store.deliveryInfo.method;
    let address = store.deliveryInfo.address;

    if (method === "courier" && selectedAddress && typeof selectedAddress === "object") {
        address = {
            street: selectedAddress.street ?? "",
            house: selectedAddress.house ?? "",
            entrance: selectedAddress.entrance ?? null,
            apartment: selectedAddress.apartment ?? null,
            latitude: selectedAddress.latitude ?? null,
            longitude: selectedAddress.longitude ?? null,
        };
    }

    return {
        method,
        address: method === "courier" ? address : null,
        comment:
            mergeCheckoutDeliveryComment(
                store.deliveryInfo.comment,
                store.customerComment,
            ) || undefined,
        scheduled_at: store.deliveryInfo.scheduledAt || undefined,
    };
}

export function buildPaymentPayload(store) {
    return {
        method: toServerCheckoutPaymentMethod(store.paymentInfo.method),
        change_from_rubles:
            store.paymentInfo.changeFrom != null
                ? Number(store.paymentInfo.changeFrom)
                : undefined,
    };
}

export function normalizePaymentPatch(patch) {
    const next = { ...(patch || {}) };
    if (next.method != null) {
        next.method = normalizeCheckoutPaymentMethod(next.method);
    }

    return next;
}
