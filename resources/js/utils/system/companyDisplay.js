import {
    findScheduleRowForDay,
    getCurrentDayKey,
    isScheduleDayOff,
} from "./companyOpenStatus";

const DAY_LABELS = {
    mon: "Пн",
    tue: "Вт",
    wed: "Ср",
    thu: "Чт",
    fri: "Пт",
    sat: "Сб",
    sun: "Вс",
};

/**
 * Обрезка пробелов только для строк; числа и прочее — через String.
 */
export function safeTrim(value) {
    if (value == null) return "";
    if (typeof value === "string") return value.trim();
    return String(value).trim();
}

/**
 * Расписание из API: массив { day, work, delivery, is_day_off } или строка.
 */
export function formatWorkScheduleForDisplay(schedule) {
    if (schedule == null) return "";
    if (typeof schedule === "string") return schedule.trim();
    if (!Array.isArray(schedule) || schedule.length === 0) return "";

    const lines = schedule.map((row) => {
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
        const delivery =
            typeof row.delivery === "string"
                ? row.delivery.trim()
                : row.delivery || "";
        const bits = [];
        if (work) bits.push(`работа ${work}`);
        if (delivery) bits.push(`доставка ${delivery}`);
        return bits.length ? `${day}: ${bits.join(", ")}` : `${day}: —`;
    });

    return lines.filter(Boolean).join(" · ");
}

/**
 * Одна строка: режим работы и доставки на указанный день (локальная дата браузера).
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
    const delivery =
        typeof row.delivery === "string"
            ? row.delivery.trim()
            : row.delivery || "";
    const bits = [];
    if (work) bits.push(work);
    if (delivery) bits.push(`доставка ${delivery}`);
    return bits.length ? `${dayLabel}: ${bits.join(", ")}` : `${dayLabel}: —`;
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
