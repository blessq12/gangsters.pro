import { formatCompanyAddressLine, safeTrim } from "./companyDisplay";
import { formatMoneyRublesRu } from "../moneyFormat";

/**
 * @param {unknown} kopecks
 * @returns {number|null}
 */
export function kopecksToRublesOptional(kopecks) {
    if (kopecks == null) return null;
    const n = Number(kopecks);
    if (!Number.isFinite(n)) return null;
    return n / 100;
}

/**
 * @param {object|null|undefined} company
 * @returns {number|null}
 */
export function averageDeliveryMinutesOrNull(company) {
    const m = company?.average_delivery_time_minutes;
    if (m == null) return null;
    const n = Number(m);
    if (!Number.isFinite(n)) return null;
    return Math.round(n);
}

/**
 * @param {object|null|undefined} company
 * @returns {string}
 */
export function formatAverageDeliveryLine(company) {
    const n = averageDeliveryMinutesOrNull(company);
    if (n == null) return "—";
    return `около ${n} мин`;
}

/**
 * @param {object|null|undefined} company
 * @returns {string}
 */
export function formatMinOrderRublesLine(company) {
    const rub = kopecksToRublesOptional(company?.min_order_amount_kopecks);
    if (rub == null) return "—";
    return `${formatMoneyRublesRu(rub)} ₽`;
}

/**
 * @param {object|null|undefined} company
 * @returns {string}
 */
export function formatDeliveryFeeRublesLine(company) {
    const rub = kopecksToRublesOptional(company?.delivery_fee_kopecks);
    if (rub == null) return "—";
    return `${formatMoneyRublesRu(rub)} ₽`;
}

/**
 * @param {object|null|undefined} company
 */
export function hasAverageDelivery(company) {
    return averageDeliveryMinutesOrNull(company) != null;
}

/**
 * @param {object|null|undefined} company
 */
export function hasMinOrder(company) {
    return kopecksToRublesOptional(company?.min_order_amount_kopecks) != null;
}

/**
 * @param {object|null|undefined} company
 */
export function hasDeliveryFee(company) {
    return kopecksToRublesOptional(company?.delivery_fee_kopecks) != null;
}

/**
 * @param {object|null|undefined} company
 */
export function hasKitchenAddressLine(company) {
    return kitchenAddressLine(company).trim() !== "";
}

/**
 * Плитки дока / hero: только поля, заданные в API (без «—»).
 * @param {object|null|undefined} company
 * @returns {{ label: string, value: string }[]}
 */
export function buildDefinedDeliveryStats(company) {
    const out = [];
    if (hasAverageDelivery(company)) {
        out.push({
            label: "Срок",
            value: formatAverageDeliveryLine(company),
        });
    }
    if (hasMinOrder(company)) {
        out.push({
            label: "Мин. заказ",
            value: formatMinOrderRublesLine(company),
        });
    }
    return out;
}

/**
 * Строки блока «Условия» в доке — только при наличии значений.
 * @param {object|null|undefined} company
 * @returns {{ label: string, value: string }[]}
 */
export function buildDefinedConditionRows(company) {
    const rows = [];
    if (hasMinOrder(company)) {
        rows.push({
            label: "Мин. заказ",
            value: formatMinOrderRublesLine(company),
        });
    }
    if (hasDeliveryFee(company)) {
        rows.push({
            label: "Доставка от",
            value: formatDeliveryFeeRublesLine(company),
        });
    }
    return rows;
}

/**
 * Статистика для SecondaryPageLayout / дока (только данные из API).
 * @param {object|null|undefined} company
 * @returns {{ label: string, value: string }[]}
 */
export function buildDeliveryHeroStats(company) {
    return [
        { label: "Срок", value: formatAverageDeliveryLine(company) },
        { label: "Мин. заказ", value: formatMinOrderRublesLine(company) },
    ];
}

/**
 * Крупное число для карточки «срок» (минуты или «—»).
 * @param {object|null|undefined} company
 * @returns {string}
 */
export function deliveryHighlightMinutesHeadline(company) {
    const n = averageDeliveryMinutesOrNull(company);
    return n != null ? String(n) : "—";
}

/**
 * @param {object|null|undefined} company
 * @returns {string}
 */
export function deliveryHighlightMinutesSubline(company) {
    return averageDeliveryMinutesOrNull(company) != null
        ? "минут — ориентировочное время доставки по данным сервиса."
        : "Точный срок зависит от адреса и загрузки — увидите при оформлении.";
}

/**
 * @param {object|null|undefined} company
 * @returns {string}
 */
export function deliveryHighlightMinOrderHeadline(company) {
    const rub = kopecksToRublesOptional(company?.min_order_amount_kopecks);
    if (rub == null) return "—";
    return `${formatMoneyRublesRu(rub)} ₽`;
}

/**
 * @param {object|null|undefined} company
 * @returns {string}
 */
export function deliveryHighlightMinOrderSubline(company) {
    return kopecksToRublesOptional(company?.min_order_amount_kopecks) != null
        ? "минимальная сумма заказа."
        : "Минимальная сумма уточняется при оформлении.";
}

/**
 * Адрес базы / кухни для карты и подписей.
 * @param {object|null|undefined} company
 * @returns {string}
 */
export function kitchenAddressLine(company) {
    return formatCompanyAddressLine(company);
}

const DEFAULT_DELIVERY_MAP_CITY = "Томск";

/**
 * URL виджета Яндекс.Карт (fallback без SDK: центр по городу кухни).
 * @param {object|null|undefined} company
 * @returns {string|null}
 */
export function buildYandexMapWidgetSearchUrl(company) {
    const city = safeTrim(company?.city) || DEFAULT_DELIVERY_MAP_CITY;
    const text = encodeURIComponent(city);
    return `https://yandex.ru/map-widget/v1/?mode=search&text=${text}&z=12`;
}

const KITCHEN_ADDRESS_FALLBACK = "Томск, ул. Говорова 50";

/**
 * Подпись адреса кухни для карты и UI.
 * @param {object|null|undefined} company
 * @returns {string}
 */
export function kitchenAddressLabelOrFallback(company) {
    const line = kitchenAddressLine(company);
    return line || KITCHEN_ADDRESS_FALLBACK;
}

/**
 * URL виджета Яндекс.Карт с точкой по адресу кухни.
 * @param {object|null|undefined} company
 * @returns {string}
 */
export function buildYandexMapKitchenPointWidgetUrl(company) {
    const text = encodeURIComponent(kitchenAddressLabelOrFallback(company));
    return `https://yandex.ru/map-widget/v1/?mode=search&text=${text}&z=16`;
}

/**
 * Способы оплаты при оформлении (cash / card), согласовано с PaymentMethod::placementValues.
 * @returns {{ id: string, title: string, description: string, icon: string }[]}
 */
export function buildCheckoutAlignedPaymentInfoBlocks() {
    return [
        {
            id: "cash",
            title: "Наличными",
            description:
                "Удобно, если предпочитаете рассчитаться при получении заказа.",
            icon: "mdi mdi-cash",
        },
        {
            id: "card",
            title: "Банковской картой",
            description:
                "При оформлении или при получении — в зависимости от доступных вариантов.",
            icon: "mdi mdi-credit-card-outline",
        },
    ];
}
