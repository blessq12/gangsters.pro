import { formatMoneyRublesRu } from "../../../platform/moneyFormat";

const STATUS_LABELS = {
    new: "Новый",
    preparing: "Готовится",
    in_transit: "В доставке",
    delivered: "Доставлен",
};

const PAYMENT_LABELS = {
    cash: "Наличные",
    card: "Карта",
    transfer: "Перевод",
};

const DELIVERY_LABELS = {
    courier: "Курьер",
    pickup: "Самовывоз",
};

export function formatOrderStatusRu(status) {
    if (!status) return "—";
    return STATUS_LABELS[status] || status;
}

export function formatPaymentMethodRu(method) {
    if (!method) return "—";
    return PAYMENT_LABELS[method] || method;
}

export function formatDeliveryMethodRu(method) {
    if (!method) return "—";
    return DELIVERY_LABELS[method] || method;
}


/** Суммы в API заказа — рубли (number), два знака после запятой */
export function formatOrderMoneyRubles(rubles) {
    return formatMoneyRublesRu(rubles);
}

export function formatOrderDate(iso) {
    if (!iso) return "—";
    try {
        return new Date(iso).toLocaleString("ru-RU", {
            day: "2-digit",
            month: "short",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        });
    } catch {
        return String(iso);
    }
}
