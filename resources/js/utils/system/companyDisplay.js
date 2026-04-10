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
