import {
    findScheduleRowForDay,
    getCurrentDayKey,
    isScheduleDayOff,
    parseWorkWindow,
} from "./companyOpenStatus";
import { formatTodayWorkScheduleLine } from "./companyDisplay";

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
