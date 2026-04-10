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
