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
