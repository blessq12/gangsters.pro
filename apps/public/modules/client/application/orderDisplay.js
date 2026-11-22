import { formatMoneyRublesRu } from "../../../platform/moneyFormat";

const STATUS_LABELS = {
    new: "Новый",
    preorder: "Предзаказ",
    preparing: "В производстве",
    ready: "Произведен",
    in_transit: "В пути",
    cancelled: "Списан",
    delivered: "Выполнен",
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

/**
 * Публичный номер заказа: ORD-XXXX-YY.
 * @param {{ number?: string|null, id?: number|string|null, created_at?: string|null }|null|undefined} order
 */
export function formatOrderNumber(order) {
    if (!order) {
        return "—";
    }
    if (typeof order.number === "string" && order.number.trim() !== "") {
        return order.number.trim();
    }
    const id = Number(order.id);
    if (!Number.isFinite(id) || id < 1) {
        return "—";
    }
    const padded = String(Math.trunc(id)).padStart(4, "0");
    if (!order.created_at) {
        return `ORD-${padded}`;
    }
    try {
        const year = new Date(order.created_at).getFullYear() % 100;
        if (Number.isNaN(year)) {
            return `ORD-${padded}`;
        }
        return `ORD-${padded}-${String(year).padStart(2, "0")}`;
    } catch {
        return `ORD-${padded}`;
    }
}

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

function pluralRu(n, one, few, many) {
    const abs = Math.abs(n) % 100;
    const n1 = abs % 10;
    if (abs > 10 && abs < 20) return many;
    if (n1 === 1) return one;
    if (n1 >= 2 && n1 <= 4) return few;
    return many;
}

/** Сколько клиент с нами (по created_at профиля). */
export function formatMembershipDurationRu(iso) {
    if (!iso) return null;
    const start = new Date(iso);
    const now = new Date();
    if (Number.isNaN(start.getTime())) return null;

    let months =
        (now.getFullYear() - start.getFullYear()) * 12 +
        (now.getMonth() - start.getMonth());
    if (now.getDate() < start.getDate()) {
        months -= 1;
    }
    if (months < 0) {
        months = 0;
    }

    if (months === 0) {
        const days = Math.max(
            0,
            Math.floor((now.getTime() - start.getTime()) / 86_400_000),
        );
        if (days <= 0) {
            return "сегодня";
        }
        return `${days} ${pluralRu(days, "день", "дня", "дней")}`;
    }

    if (months < 12) {
        return `${months} ${pluralRu(months, "месяц", "месяца", "месяцев")}`;
    }

    const years = Math.floor(months / 12);
    const remMonths = months % 12;
    const yearsPart = `${years} ${pluralRu(years, "год", "года", "лет")}`;
    if (remMonths === 0) {
        return yearsPart;
    }
    return `${yearsPart} ${remMonths} ${pluralRu(remMonths, "месяц", "месяца", "месяцев")}`;
}
