import { computed, onMounted, onUnmounted, ref } from "vue";
import { formatMoneyRublesRu } from "../../../platform/moneyFormat";

/**
 * Логика «сейчас открыто» по данным компании из API.
 * Опирается на schedule: объект { mon: { work, is_day_off }, … }.
 * Время в work парсится как «10:00-22:00» (дефис или длинное тире).
 * Часовой пояс — локальный (браузер пользователя).
 */

const DAY_KEYS_JS = ["sun", "mon", "tue", "wed", "thu", "fri", "sat"];

/** @param {Date} [date] */
export function getCurrentDayKey(date = new Date()) {
    return DAY_KEYS_JS[date.getDay()];
}

/**
 * @param {string} segment
 * @returns {number|null} минуты от полуночи
 */
function parseHHMM(segment) {
    const m = /^(\d{1,2}):(\d{2})$/.exec(String(segment).trim());
    if (!m) return null;
    const h = Number(m[1]);
    const min = Number(m[2]);
    if (!Number.isInteger(h) || !Number.isInteger(min)) return null;
    if (h < 0 || h > 23 || min < 0 || min > 59) return null;
    return h * 60 + min;
}

/**
 * @param {string|null|undefined} workRaw
 * @returns {{ start: number, end: number }|null}
 */
export function parseWorkWindow(workRaw) {
    if (workRaw == null || typeof workRaw !== "string") return null;
    const s = workRaw.trim();
    if (!s) return null;
    const parts = s
        .split(/[-–—]/u)
        .map((p) => p.trim())
        .filter(Boolean);
    if (parts.length < 2) return null;
    const start = parseHHMM(parts[0]);
    const end = parseHHMM(parts[1]);
    if (start == null || end == null) return null;
    return { start, end };
}

/**
 * @param {unknown} row
 */
export function isScheduleDayOff(row) {
    if (!row || typeof row !== "object") return true;
    const v = row.is_day_off;
    return v === "1" || v === 1 || v === true;
}

/**
 * @param {unknown} schedule — объект по дням или (legacy) массив
 * @param {string} dayKey
 */
export function findScheduleRowForDay(schedule, dayKey) {
    if (!schedule || !dayKey) return null;

    if (Array.isArray(schedule)) {
        return (
            schedule.find(
                (r) => r && typeof r === "object" && r.day === dayKey,
            ) || null
        );
    }

    if (typeof schedule !== "object") return null;
    const cell = schedule[dayKey];
    if (!cell || typeof cell !== "object") return null;

    return {
        day: dayKey,
        work: cell.work ?? null,
        is_day_off: cell.is_day_off,
    };
}

/**
 * @param {object|null|undefined} company
 * @returns {unknown}
 */
export function companyScheduleOf(company) {
    return company?.schedule ?? null;
}

/**
 * Сейчас в интервале [start, end] в минутах от полуночи?
 * @param {Date} now
 * @param {number} startMin
 * @param {number} endMin
 */
export function isTimeWithinWorkWindow(now, startMin, endMin) {
    const mins = now.getHours() * 60 + now.getMinutes();
    return mins >= startMin && mins <= endMin;
}

/**
 * Нет строки расписания: сб/вс считаем закрытыми, пн–пт — открытыми (без проверки часов).
 * @param {Date} now
 */
export function isOpenByWeekendFallback(now) {
    const d = now.getDay();
    return d !== 0 && d !== 6;
}

/**
 * @param {object|null|undefined} company — объект из systemStore.company
 * @param {Date} [now]
 * @returns {boolean}
 */
export function isCompanyOpenNow(company, now = new Date()) {
    const dayKey = getCurrentDayKey(now);
    const schedule = companyScheduleOf(company);
    const row = findScheduleRowForDay(schedule, dayKey);

    if (row) {
        if (isScheduleDayOff(row)) {
            return false;
        }
        const win = parseWorkWindow(row.work);
        if (win) {
            return isTimeWithinWorkWindow(now, win.start, win.end);
        }
        const workStr = typeof row.work === "string" ? row.work.trim() : "";
        if (!workStr) {
            return isOpenByWeekendFallback(now);
        }
        return isOpenByWeekendFallback(now);
    }

    if (
        schedule &&
        typeof schedule === "object" &&
        Object.keys(schedule).length > 0
    ) {
        return isOpenByWeekendFallback(now);
    }

    return isOpenByWeekendFallback(now);
}

/**
 * @param {object|null|undefined} company
 * @param {Date} [now]
 * @returns {{ open: boolean, hint: string }}
 */
export function getCompanyOpenStatusHint(company, now = new Date()) {
    const open = isCompanyOpenNow(company, now);
    // Статус «Открыто/Закрыто» рисует UI; доп. hint (время) не отдаём.
    return { open, hint: "" };
}

const DAY_LABELS = {
    mon: "Пн",
    tue: "Вт",
    wed: "Ср",
    thu: "Чт",
    fri: "Пт",
    sat: "Сб",
    sun: "Вс",
};

const DAY_ORDER = ["mon", "tue", "wed", "thu", "fri", "sat", "sun"];

/**
 * Структурированные строки расписания для UI (карточки, сетки).
 * @typedef {{ dayKey: string|null, dayLabel: string, isDayOff: boolean, work: string|null, isFallbackString?: boolean }} WorkScheduleRow
 */

/**
 * @param {unknown} schedule
 * @returns {WorkScheduleRow[]}
 */
export function getWorkScheduleRows(schedule) {
    if (schedule == null) return [];
    if (typeof schedule === "string") {
        const t = schedule.trim();
        if (!t) return [];
        return [
            {
                dayKey: null,
                dayLabel: "",
                isDayOff: false,
                work: t,
                isFallbackString: true,
            },
        ];
    }

    /** @type {Array<{ day: string, work: unknown, is_day_off: unknown }>} */
    let list = [];
    if (Array.isArray(schedule)) {
        list = schedule.filter((r) => r && typeof r === "object");
    } else if (typeof schedule === "object") {
        list = DAY_ORDER.map((day) => {
            const cell = schedule[day];
            if (!cell || typeof cell !== "object") {
                return { day, work: null, is_day_off: false };
            }
            return {
                day,
                work: cell.work ?? null,
                is_day_off: cell.is_day_off,
            };
        });
    } else {
        return [];
    }

    if (list.length === 0) return [];

    const rows = list
        .map((row) => {
            if (!row || typeof row !== "object") return null;
            const dayKey = typeof row.day === "string" ? row.day : null;
            const dayLabel = (dayKey && DAY_LABELS[dayKey]) || dayKey || "—";
            const off =
                row.is_day_off === "1" ||
                row.is_day_off === 1 ||
                row.is_day_off === true;
            const workRaw =
                typeof row.work === "string" ? row.work.trim() : row.work || "";
            const work = workRaw ? String(workRaw) : null;
            return {
                dayKey,
                dayLabel,
                isDayOff: off,
                work,
            };
        })
        .filter(Boolean);

    rows.sort((a, b) => {
        const ia = a.dayKey ? DAY_ORDER.indexOf(a.dayKey) : -1;
        const ib = b.dayKey ? DAY_ORDER.indexOf(b.dayKey) : -1;
        const sa = ia === -1 ? 99 : ia;
        const sb = ib === -1 ? 99 : ib;
        return sa - sb;
    });

    return rows;
}

/**
 * Обрезка пробелов только для строк; числа и прочее — через String.
 */
export function safeTrim(value) {
    if (value == null) return "";
    if (typeof value === "string") return value.trim();
    return String(value).trim();
}

function scheduleRowToDisplayLine(row) {
    if (!row || typeof row !== "object") return "";
    const dayKey = row.day;
    const day = DAY_LABELS[dayKey] || dayKey || "—";
    const off =
        row.is_day_off === "1" ||
        row.is_day_off === 1 ||
        row.is_day_off === true;
    if (off) {
        return `${day}: выходной`;
    }
    const work =
        typeof row.work === "string" ? row.work.trim() : row.work || "";
    if (work) {
        return `${day}: ${work}`;
    }
    return `${day}: —`;
}

/**
 * Строки по дням для списка (например поповер в навбаре).
 * @param {unknown} schedule
 * @returns {string[]}
 */
export function getWorkScheduleLines(schedule) {
    if (schedule == null) return [];
    if (typeof schedule === "string") {
        const t = schedule.trim();
        return t ? [t] : [];
    }

    const rows = getWorkScheduleRows(schedule);
    return rows.map((row) => {
        const day = row.dayLabel || "—";
        if (row.isDayOff) return `${day}: выходной`;
        if (row.work) return `${day}: ${row.work}`;
        return `${day}: —`;
    });
}

/**
 * Расписание из API: объект по дням (или legacy-массив).
 */
export function formatWorkScheduleForDisplay(schedule) {
    const lines = getWorkScheduleLines(schedule);
    return lines.length ? lines.join(" · ") : "";
}

/**
 * Одна строка: режим работы на указанный день (локальная дата браузера).
 * @param {object|null|undefined} company
 * @param {Date} [date]
 */
export function formatTodayWorkScheduleLine(company, date = new Date()) {
    if (!company) return "";

    const dayKey = getCurrentDayKey(date);
    const dayLabel = DAY_LABELS[dayKey] || dayKey;
    const row = findScheduleRowForDay(companyScheduleOf(company), dayKey);

    if (!row) {
        return "";
    }

    if (isScheduleDayOff(row)) {
        return `${dayLabel}: выходной`;
    }

    const work =
        typeof row.work === "string" ? row.work.trim() : row.work || "";
    if (work) {
        return `${dayLabel}: ${work}`;
    }
    return `${dayLabel}: —`;
}

/**
 * Краткая однострочная строка адреса для подписи в UI.
 * @param {object|null|undefined} company
 */
export function formatCompanyAddressLine(company) {
    if (!company) return "";

    const city = safeTrim(company.city);
    const street = safeTrim(company.street);
    const house = safeTrim(company.house);
    const line = [street, house].filter(Boolean).join(" ");
    const core = city && line ? `${city}, ${line}` : city || line || "";

    const comment = safeTrim(company.address_comment);
    if (!core) {
        return comment;
    }
    return comment ? `${core} · ${comment}` : core;
}

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
    return `https://yandex.ru/map-widget/v1/?mode=search&text=${text}&z=12&theme=dark`;
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
    return `https://yandex.ru/map-widget/v1/?mode=search&text=${text}&z=16&theme=dark`;
}

/**
 * Способы оплаты на странице доставки (инфо-блоки).
 * @returns {{ id: string, title: string, description: string, icon: string, unavailable?: boolean }[]}
 */
export function buildCheckoutAlignedPaymentInfoBlocks() {
    return [
        {
            id: "cash",
            title: "Наличными при получении",
            description: "Оплата курьеру при доставке заказа.",
            icon: "mdi mdi-cash",
        },
        {
            id: "card_online",
            title: "Банковской картой онлайн",
            description: "Оплата картой на сайте.",
            icon: "mdi mdi-credit-card-wireless-outline",
            unavailable: true,
        },
        {
            id: "card_courier",
            title: "Банковской картой при получении",
            description: "Оплата картой курьеру при доставке.",
            icon: "mdi mdi-credit-card-outline",
        },
        {
            id: "qr",
            title: "По QR-коду",
            description: "Оплата по QR-коду при получении заказа.",
            icon: "mdi mdi-qrcode",
        },
    ];
}

export const CLOSED_NOTICE_DISMISSED_KEY =
    "gangsters_closed_notice_dismissed_v1";

export function wasClosedNoticeDismissedThisSession() {
    if (typeof window === "undefined") {
        return false;
    }

    return window.sessionStorage.getItem(CLOSED_NOTICE_DISMISSED_KEY) === "1";
}

export function markClosedNoticeDismissedThisSession() {
    if (typeof window === "undefined") {
        return;
    }

    window.sessionStorage.setItem(CLOSED_NOTICE_DISMISSED_KEY, "1");
}

/**
 * @param {object|null|undefined} company
 * @param {Date} [now]
 * @returns {{
 *   title: string,
 *   lead: string,
 *   todayLine: string|null,
 * }}
 */
export function buildClosedOrdersNotice(company, now = new Date()) {
    const todayLine = formatTodayWorkScheduleLine(company, now) || null;

    const scheduleFallback = todayLine;

    const dayKey = getCurrentDayKey(now);
    const row = findScheduleRowForDay(companyScheduleOf(company), dayKey);
    let lead =
        "Сейчас мы не принимаем и не обрабатываем заказы. Оформи заказ в рабочие часы — тогда всё уйдёт на кухню без задержек.";

    if (row && isScheduleDayOff(row)) {
        lead =
            "Сегодня у нас выходной — заказы не принимаем. Загляни в рабочий день по расписанию ниже.";
    } else if (row && parseWorkWindow(row.work)) {
        lead =
            "Сейчас вне часов работы — заказы не принимаем. Оформи заказ, когда мы открыты по расписанию.";
    }

    return {
        title: "Сейчас не принимаем заказы",
        lead,
        todayLine: scheduleFallback,
    };
}

const TICK_MS = 60_000;

/**
 * Реактивный статус открытости по данным компании (обновляется раз в минуту).
 * @param {() => object|null|undefined} getCompany
 */
export function useCompanyOpenStatus(getCompany) {
    const tick = ref(0);
    let timerId = null;

    onMounted(() => {
        timerId = window.setInterval(() => {
            tick.value += 1;
        }, TICK_MS);
    });

    onUnmounted(() => {
        if (timerId != null) {
            window.clearInterval(timerId);
        }
    });

    const openNow = computed(() => {
        void tick.value;
        const c = getCompany();
        return isCompanyOpenNow(c, new Date());
    });

    const statusHint = computed(() => {
        void tick.value;
        const c = getCompany();
        return getCompanyOpenStatusHint(c, new Date());
    });

    return { openNow, statusHint, tick };
}
