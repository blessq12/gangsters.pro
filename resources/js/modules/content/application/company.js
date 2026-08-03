import { computed, onMounted, onUnmounted, ref } from "vue";
import { formatMoneyRublesRu } from "../../../platform/moneyFormat";

/**
 * Логика «сейчас открыто» по данным компании из API.
 * Опирается на work_schedule: массив { day, work, is_day_off }.
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
    const parts = s.split(/[-–—]/u).map((p) => p.trim()).filter(Boolean);
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
 * @param {unknown} schedule
 * @param {string} dayKey
 */
export function findScheduleRowForDay(schedule, dayKey) {
    if (!Array.isArray(schedule) || !dayKey) return null;
    return schedule.find((r) => r && typeof r === "object" && r.day === dayKey) || null;
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
    const schedule = company?.work_schedule;
    const row = findScheduleRowForDay(schedule, dayKey);

    if (row) {
        if (isScheduleDayOff(row)) {
            return false;
        }
        const win = parseWorkWindow(row.work);
        if (win) {
            return isTimeWithinWorkWindow(now, win.start, win.end);
        }
        const workStr =
            typeof row.work === "string" ? row.work.trim() : "";
        if (!workStr) {
            return isOpenByWeekendFallback(now);
        }
        return isOpenByWeekendFallback(now);
    }

    if (Array.isArray(schedule) && schedule.length > 0) {
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
    const dayKey = getCurrentDayKey(now);
    const row = findScheduleRowForDay(company?.work_schedule, dayKey);

    if (row && isScheduleDayOff(row)) {
        return { open: false, hint: "Выходной" };
    }

    const w =
        row && row.work != null ? parseWorkWindow(row.work) : null;
    if (w) {
        if (open) {
            const eh = String(Math.floor(w.end / 60)).padStart(2, "0");
            const em = String(w.end % 60).padStart(2, "0");
            return { open: true, hint: `До ${eh}:${em}` };
        }
        return { open: false, hint: "Вне часов работы" };
    }

    if (open) {
        return { open: true, hint: "Работаем" };
    }
    return { open: false, hint: "Закрыто" };
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
    if (!Array.isArray(schedule) || schedule.length === 0) return [];

    const rows = schedule
        .map((row) => {
            if (!row || typeof row !== "object") return null;
            const dayKey =
                typeof row.day === "string" ? row.day : null;
            const dayLabel =
                (dayKey && DAY_LABELS[dayKey]) || dayKey || "—";
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
    if (!Array.isArray(schedule) || schedule.length === 0) return [];
    return schedule.map(scheduleRowToDisplayLine).filter(Boolean);
}

/**
 * Расписание из API: массив { day, work, is_day_off } или строка.
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
    const schedule = company.work_schedule;
    const row =
        Array.isArray(schedule) && schedule.length > 0
            ? findScheduleRowForDay(schedule, dayKey)
            : null;

    if (!row) {
        const wh = safeTrim(company.work_hours);
        return wh ? `Сегодня: ${wh}` : "";
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
    const core =
        city && line ? `${city}, ${line}` : city || line || "";

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

export const CLOSED_NOTICE_DISMISSED_KEY = "gangsters_closed_notice_dismissed_v1";

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
    const workHours =
        typeof company?.work_hours === "string" ? company.work_hours.trim() : "";

    const scheduleFallback =
        todayLine ||
        (workHours ? `Режим работы: ${workHours}` : null);

    const dayKey = getCurrentDayKey(now);
    const row = findScheduleRowForDay(company?.work_schedule, dayKey);
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
