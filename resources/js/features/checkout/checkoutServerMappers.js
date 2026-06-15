import {
    fromServerCheckoutPaymentMethod,
    normalizeCheckoutPaymentMethod,
    toServerCheckoutPaymentMethod,
    CHECKOUT_PAYMENT_METHOD_LABELS,
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

function normalizeDeliveryCoordinate(value) {
    if (value == null || value === "") {
        return null;
    }

    const number = Number(value);
    if (!Number.isFinite(number)) {
        return null;
    }

    return number;
}

function deliveryCoordinatesAreUsable(latitude, longitude) {
    if (latitude == null || longitude == null) {
        return false;
    }

    if (latitude === 0 && longitude === 0) {
        return false;
    }

    return Math.abs(latitude) <= 90 && Math.abs(longitude) <= 180;
}

function buildDeliveryAddressPayload(source) {
    if (!source || typeof source !== "object") {
        return null;
    }

    const latitude = normalizeDeliveryCoordinate(source.latitude);
    const longitude = normalizeDeliveryCoordinate(source.longitude);
    const payload = {
        street: source.street ?? "",
        house: source.house ?? "",
        entrance: source.entrance ?? null,
        apartment: source.apartment ?? null,
    };

    if (deliveryCoordinatesAreUsable(latitude, longitude)) {
        payload.latitude = latitude;
        payload.longitude = longitude;
    }

    return payload;
}

export function buildDeliveryPayload(store, selectedAddress = null) {
    const method = store.deliveryInfo.method;
    let address = store.deliveryInfo.address;

    if (method === "courier" && selectedAddress && typeof selectedAddress === "object") {
        address = buildDeliveryAddressPayload(selectedAddress);
    } else if (method === "courier" && address && typeof address === "object") {
        address = buildDeliveryAddressPayload(address);
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

/**
 * @param {object|null|undefined} delivery
 */
export function formatServerDeliveryLine(delivery) {
    if (!delivery || typeof delivery !== "object") {
        return "—";
    }

    if (delivery.method === "pickup") {
        return "Самовывоз";
    }

    const address = delivery.address;
    if (!address || typeof address !== "object") {
        return "Курьер";
    }

    return [
        address.street,
        address.house && `д. ${address.house}`,
        address.apartment && `кв. ${address.apartment}`,
    ]
        .filter(Boolean)
        .join(", ");
}

/**
 * @param {object|null|undefined} client
 * @param {(phone: string) => string} formatPhone
 */
export function formatServerClientLine(client, formatPhone) {
    if (!client || typeof client !== "object") {
        return "—";
    }

    const name = String(client.name || "").trim() || "—";
    const phone = client.phone ? formatPhone(String(client.phone)) : "—";

    return `${name}, ${phone}`;
}

/**
 * @param {object|null|undefined} payment
 * @param {(value: number) => string} formatPrice
 */
export function formatServerPaymentLine(payment, formatPrice) {
    if (!payment || typeof payment !== "object") {
        return "—";
    }

    const method = fromServerCheckoutPaymentMethod(payment.method);
    const label = CHECKOUT_PAYMENT_METHOD_LABELS[method] ?? method;

    if (method === "cash" && payment.change_from_rubles != null) {
        return `${label} · сдача с ${formatPrice(Number(payment.change_from_rubles))} ₽`;
    }

    return label;
}

/**
 * @param {object|null|undefined} product
 * @param {object|null|undefined} existingPayload
 * @returns {object|null}
 */
export function buildCatalogCartLinePayload(product, existingPayload = null) {
    if (existingPayload && typeof existingPayload === "object") {
        return existingPayload;
    }

    if (product?.kind === "set") {
        return { catalog_kind: "set" };
    }

    return null;
}
