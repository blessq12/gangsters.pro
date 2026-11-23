/**
 * Состояние заказа (State): подпись этапа и следующая команда.
 * Экран рисует только то, что вернули эти функции. Прямой смены статуса нет.
 */

export const ORDER_STATUSES = [
    "new",
    "preorder",
    "confirmed",
    "preparing",
    "ready",
    "assigned_to_route",
    "in_transit",
    "delivered",
    "written_off",
    "cancelled",
];

const ORDER_STATUS_LABEL = {
    new: "Новый",
    preorder: "Предзаказ",
    confirmed: "Подтверждён",
    preparing: "Готовится",
    ready: "Готов",
    assigned_to_route: "В маршрутном листе",
    in_transit: "В пути",
    delivered: "Выдан",
    written_off: "Списан",
    cancelled: "Отменён",
};

const ORDER_STATUS_VARIANT = {
    new: "neutral",
    preorder: "neutral",
    confirmed: "warning",
    preparing: "warning",
    ready: "success",
    assigned_to_route: "warning",
    in_transit: "warning",
    delivered: "success",
    written_off: "danger",
    cancelled: "danger",
};

const PAYMENT_STATUS_LABEL = {
    pending: "Ожидает",
    paid: "Оплачен",
    refunded: "Возврат",
    cancelled: "Отменён",
};

const PAYMENT_STATUS_VARIANT = {
    pending: "warning",
    paid: "success",
    refunded: "neutral",
    cancelled: "danger",
};

const PAYMENT_METHOD_LABEL = {
    cash: "Наличные",
    card_courier: "Карта курьеру",
    card_online: "Карта онлайн",
};

const TICKET_STATUS_LABEL = {
    queued: "В очереди",
    started: "В работе",
    completed: "Готов",
    cancelled: "Отменён",
};

const TICKET_STATUS_VARIANT = {
    queued: "warning",
    started: "neutral",
    completed: "success",
    cancelled: "danger",
};

const SHEET_STATUS_LABEL = {
    released: "Выпущен",
    in_delivery: "В доставке",
    completed: "Закрыт",
};

const SHEET_STATUS_VARIANT = {
    released: "warning",
    in_delivery: "warning",
    completed: "success",
};

const STOP_OUTCOME_LABEL = {
    pending: "Ожидает",
    delivered: "Доставлен",
    written_off: "Списан",
    cancelled: "Отменена",
};

const STOP_OUTCOME_VARIANT = {
    pending: "neutral",
    delivered: "success",
    written_off: "danger",
    cancelled: "danger",
};

const CANCELLABLE = [
    "new",
    "preorder",
    "confirmed",
    "preparing",
    "ready",
    "assigned_to_route",
];

const CLOSED_ORDER = ["delivered", "written_off", "cancelled"];

/** @param {string|null|undefined} status */
export function orderStatusLabel(status) {
    return ORDER_STATUS_LABEL[status] ?? "Неизвестный этап";
}

/** @param {string|null|undefined} status */
export function orderStatusVariant(status) {
    return ORDER_STATUS_VARIANT[status] ?? "neutral";
}

/** @param {string|null|undefined} status */
export function paymentStatusLabel(status) {
    return PAYMENT_STATUS_LABEL[status] ?? "Нет платежа";
}

/** @param {string|null|undefined} status */
export function paymentStatusVariant(status) {
    return PAYMENT_STATUS_VARIANT[status] ?? "neutral";
}

/** @param {string|null|undefined} method */
export function paymentMethodLabel(method) {
    return PAYMENT_METHOD_LABEL[method] ?? "—";
}

/** @param {string|null|undefined} status */
export function ticketStatusLabel(status) {
    return TICKET_STATUS_LABEL[status] ?? "—";
}

/** @param {string|null|undefined} status */
export function ticketStatusVariant(status) {
    return TICKET_STATUS_VARIANT[status] ?? "neutral";
}

/** @param {string|null|undefined} status */
export function sheetStatusLabel(status) {
    return SHEET_STATUS_LABEL[status] ?? "—";
}

/** @param {string|null|undefined} status */
export function sheetStatusVariant(status) {
    return SHEET_STATUS_VARIANT[status] ?? "neutral";
}

/** @param {string|null|undefined} outcome */
export function stopOutcomeLabel(outcome) {
    const key = outcome && outcome !== "" ? outcome : "pending";
    return STOP_OUTCOME_LABEL[key] ?? key;
}

/** @param {string|null|undefined} outcome */
export function stopOutcomeVariant(outcome) {
    const key = outcome && outcome !== "" ? outcome : "pending";
    return STOP_OUTCOME_VARIANT[key] ?? "neutral";
}

/** @param {string|null|undefined} method */
export function deliveryMethodLabel(method) {
    if (method === "pickup") {
        return "Самовывоз";
    }
    if (method === "courier") {
        return "Курьер";
    }
    return "—";
}

/**
 * Публичный номер: ORD-XXXX-YY.
 * @param {number|string|null|undefined} id
 * @param {string|Date|null|undefined} createdAt
 */
export function formatOrderNumber(id, createdAt) {
    const numericId = Number(id);
    if (!Number.isFinite(numericId) || numericId < 1) {
        return "—";
    }
    const padded = String(Math.trunc(numericId)).padStart(4, "0");
    if (!createdAt) {
        return `ORD-${padded}`;
    }
    try {
        const year = new Date(createdAt).getFullYear() % 100;
        if (Number.isNaN(year)) {
            return `ORD-${padded}`;
        }
        return `ORD-${padded}-${String(year).padStart(2, "0")}`;
    } catch {
        return `ORD-${padded}`;
    }
}

/**
 * @param {{ number?: string|null, id?: number|string|null, created_at?: string|null, order_number?: string|null, order_id?: number|string|null }|null|undefined} order
 */
export function orderNumberOf(order) {
    if (!order) {
        return "—";
    }
    if (typeof order.number === "string" && order.number.trim() !== "") {
        return order.number.trim();
    }
    if (typeof order.order_number === "string" && order.order_number.trim() !== "") {
        return order.order_number.trim();
    }
    return formatOrderNumber(order.id ?? order.order_id, order.created_at);
}

/** @param {Record<string, unknown>|null|undefined} order */
export function formatRubles(order) {
    const rubles = order?.totals?.grand_total_rubles;
    if (rubles === undefined || rubles === null || Number.isNaN(Number(rubles))) {
        return "—";
    }
    return `${rubles} ₽`;
}

/** @param {Record<string, unknown>|null|undefined} line */
export function formatLineRubles(line) {
    const kopecks = Number(line?.line_total_kopecks ?? 0);
    return `${Math.floor(kopecks / 100)} ₽`;
}

/** @param {number|null|undefined} kopecks */
export function formatKopecks(kopecks) {
    if (kopecks === undefined || kopecks === null) {
        return "—";
    }
    return `${Math.floor(Number(kopecks) / 100)} ₽`;
}

/**
 * @param {{ street?: string, house?: string, entrance?: string|null, floor?: string|null, apartment?: string|null }|null|undefined} address
 */
export function formatAddress(address) {
    if (!address || !address.street) {
        return null;
    }
    const parts = [`${address.street}, ${address.house ?? ""}`.trim()];
    if (address.entrance) {
        parts.push(`подъезд ${address.entrance}`);
    }
    if (address.floor) {
        parts.push(`этаж ${address.floor}`);
    }
    if (address.apartment) {
        parts.push(`кв. ${address.apartment}`);
    }
    return parts.join(", ");
}

/**
 * @param {{ id?: number, name?: string, city?: string, street?: string, house?: string }|null|undefined} branch
 */
export function branchLabel(branch) {
    if (!branch?.id) {
        return "—";
    }
    const title = branch.name ? String(branch.name) : `Филиал #${branch.id}`;
    const place = [branch.city, branch.street, branch.house].filter(Boolean).join(", ");
    return place ? `${title} · ${place}` : title;
}

/**
 * Коротко: какое следующее действие по заказу.
 * @param {Record<string, unknown>|null|undefined} order
 */
export function shortStage(order) {
    const status = order?.status;
    const method = order?.delivery?.method;
    switch (status) {
        case "new":
        case "preorder":
            return "Подтвердить";
        case "confirmed":
            return "Старт тикета";
        case "preparing":
            return "Завершить тикет";
        case "ready":
            if (method === "pickup") {
                return "Выдать самовывоз";
            }
            if (method === "courier") {
                return "Собрать маршрутный лист";
            }
            return "Готов";
        case "assigned_to_route":
            return "Взять маршрутный лист";
        case "in_transit":
            return "Закрыть остановку";
        case "delivered":
            return "Завершён";
        case "written_off":
            return "Списан";
        case "cancelled":
            return "Отменён";
        default:
            return "—";
    }
}

/**
 * Где заказ и что произойдёт дальше. Без кнопок.
 * @param {Record<string, unknown>|null|undefined} order
 */
export function stageHint(order) {
    const status = order?.status;
    const method = order?.delivery?.method;
    switch (status) {
        case "new":
            return "Новый заказ. Следующее действие администратора — подтвердить и назначить филиал. После этого появятся тикет и платёж.";
        case "preorder":
            return "Предзаказ. Следующее действие администратора — подтвердить и назначить филиал.";
        case "confirmed":
            return "Подтверждён, тикет стоит в очереди. Следующее действие кухни — старт тикета.";
        case "preparing":
            return "Кухня готовит. Следующее действие — завершить тикет, заказ станет готов.";
        case "ready":
            if (method === "pickup") {
                return "Готов к выдаче. Следующее действие — выдать самовывоз. Платёж закроется вместе с выдачей.";
            }
            if (method === "courier") {
                return "Готов к доставке. Следующее действие — собрать маршрутный лист. Самовывозом такой заказ не выдаётся.";
            }
            return "Заказ готов.";
        case "assigned_to_route":
            return "Заказ в маршрутном листе. Следующее действие — курьер берёт лист, заказ уйдёт в путь.";
        case "in_transit":
            return "Курьер в пути. Следующее действие — закрыть остановку: доставлен или списан.";
        case "delivered":
            return "Заказ выдан. Дальше действий нет.";
        case "written_off":
            return "Заказ списан на остановке. Дальше действий нет.";
        case "cancelled":
            return "Заказ отменён. Тикет, остановка и платёж закрываются вместе с отменой.";
        default:
            return "Этап заказа не распознан.";
    }
}

/**
 * Команды администратора. Кухня и курьер сюда не входят: их действия на своих экранах.
 * @param {Record<string, unknown>|null|undefined} order
 * @returns {Array<{ id: "confirm"|"pickup"|"toRoute"|"cancel", label: string }>}
 */
export function administratorCommands(order) {
    const status = order?.status;
    const method = order?.delivery?.method;
    /** @type {Array<{ id: "confirm"|"pickup"|"toRoute"|"cancel", label: string }>} */
    const commands = [];

    if (status === "new" || status === "preorder") {
        commands.push({ id: "confirm", label: "Подтвердить" });
    }
    if (status === "ready" && method === "pickup") {
        commands.push({ id: "pickup", label: "Выдать самовывоз" });
    }
    if (status === "ready" && method === "courier") {
        commands.push({ id: "toRoute", label: "В маршрутный лист" });
    }
    if (CANCELLABLE.includes(status)) {
        commands.push({ id: "cancel", label: "Отменить" });
    }

    return commands;
}

/**
 * Безопасные команды owner: только отмена, без кухни/маршрутов.
 * @param {Record<string, unknown>|null|undefined} order
 * @returns {Array<{ id: "cancel", label: string }>}
 */
export function ownerCommands(order) {
    /** @type {Array<{ id: "cancel", label: string }>} */
    const commands = [];
    if (CANCELLABLE.includes(order?.status)) {
        commands.push({ id: "cancel", label: "Отменить" });
    }
    return commands;
}

/**
 * @param {Record<string, unknown>|null|undefined} order
 */
export function paymentHint(order) {
    const payment = order?.payment;
    const status = order?.status;
    if (!payment) {
        if (status === "new" || status === "preorder") {
            return "Платёж появится после подтверждения.";
        }
        return "Платёж к заказу не привязан.";
    }
    if (payment.status === "pending" && CLOSED_ORDER.includes(status)) {
        return "Платёж всё ещё ожидает. После выдачи, доставки или отмены он должен закрыться.";
    }
    if (payment.status === "pending") {
        return "Ожидает. Закроется при выдаче, доставке или отмене — отдельной кнопки оплаты нет.";
    }
    if (payment.status === "paid") {
        return "Оплачен.";
    }
    if (payment.status === "cancelled") {
        return "Отменён вместе с заказом.";
    }
    if (payment.status === "refunded") {
        return "Возврат.";
    }
    return "Статус платежа не распознан.";
}

/** @param {Record<string, unknown>|null|undefined} order */
export function paymentLooksStuck(order) {
    return order?.payment?.status === "pending" && CLOSED_ORDER.includes(order?.status);
}

/** @param {string|null|undefined} status */
export function ticketHint(status) {
    switch (status) {
        case "queued":
            return "Тикет в очереди. Следующее действие — старт. Заказ перейдёт в «Готовится».";
        case "started":
            return "Тикет в работе. Следующее действие — завершить. Заказ станет «Готов».";
        case "completed":
            return "Тикет завершён. Дальше заказ забирает администратор или маршрутный лист.";
        case "cancelled":
            return "Тикет отменён вместе с заказом.";
        default:
            return "Этап тикета не распознан.";
    }
}

/**
 * @param {Record<string, unknown>|null|undefined} sheet
 */
export function sheetHint(sheet) {
    const status = sheet?.status;
    const pending = (sheet?.stops ?? []).some((stop) => !stop.outcome || stop.outcome === "pending");
    if (status === "released") {
        return "Лист выпущен. Следующее действие — взять его. Заказы уйдут в путь.";
    }
    if (status === "in_delivery" && pending) {
        return "Лист в доставке. Следующее действие — закрыть остановку: доставлен или списан.";
    }
    if (status === "in_delivery") {
        return "Ожидающие остановки закрыты. Лист закроется вместе с последней остановкой.";
    }
    if (status === "completed") {
        return "Маршрутный лист закрыт.";
    }
    return "Этап маршрутного листа не распознан.";
}

/** @param {string|null|undefined} outcome */
export function stopIsOpen(outcome) {
    return !outcome || outcome === "pending";
}
