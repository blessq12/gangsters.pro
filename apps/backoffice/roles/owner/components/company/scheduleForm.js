export const SCHEDULE_DAYS = [
    { key: "mon", label: "Понедельник" },
    { key: "tue", label: "Вторник" },
    { key: "wed", label: "Среда" },
    { key: "thu", label: "Четверг" },
    { key: "fri", label: "Пятница" },
    { key: "sat", label: "Суббота" },
    { key: "sun", label: "Воскресенье" },
];

/** @returns {{ key: string, label: string, is_day_off: boolean, open: string, close: string }[]} */
export function emptyScheduleRows() {
    return SCHEDULE_DAYS.map((day) => ({
        key: day.key,
        label: day.label,
        is_day_off: false,
        open: "11:00",
        close: "22:00",
    }));
}

/**
 * @param {string|null|undefined} work
 * @returns {{ open: string, close: string }}
 */
export function parseWorkWindow(work) {
    if (!work || typeof work !== "string") {
        return { open: "", close: "" };
    }
    const parts = work.split(/[-–—]/u).map((part) => part.trim()).filter(Boolean);
    return {
        open: parts[0] ?? "",
        close: parts[1] ?? "",
    };
}

/**
 * @param {Record<string, { work?: string|null, is_day_off?: boolean }>|null|undefined} schedule
 */
export function scheduleToRows(schedule) {
    return SCHEDULE_DAYS.map((day) => {
        const row = schedule?.[day.key] ?? {};
        const times = parseWorkWindow(row.work);
        return {
            key: day.key,
            label: day.label,
            is_day_off: Boolean(row.is_day_off),
            open: times.open || "11:00",
            close: times.close || "22:00",
        };
    });
}

/**
 * @param {{ key: string, is_day_off: boolean, open: string, close: string }[]} rows
 */
export function rowsToSchedule(rows) {
    /** @type {Record<string, { work: string|null, is_day_off: boolean }>} */
    const out = {};
    for (const row of rows) {
        const open = String(row.open || "").trim();
        const close = String(row.close || "").trim();
        out[row.key] = {
            is_day_off: Boolean(row.is_day_off),
            work: row.is_day_off || (!open && !close) ? null : `${open}-${close}`,
        };
    }
    return out;
}

/**
 * @param {{ key: string, label: string, is_day_off: boolean, open: string, close: string }[]} rows
 * @returns {string|null}
 */
export function validateScheduleRows(rows) {
    const timeRe = /^([01]\d|2[0-3]):[0-5]\d$/;
    for (const row of rows) {
        if (row.is_day_off) {
            continue;
        }
        if (!timeRe.test(String(row.open || "").trim()) || !timeRe.test(String(row.close || "").trim())) {
            return `Проверьте время для «${row.label}» (ЧЧ:ММ)`;
        }
    }
    return null;
}
