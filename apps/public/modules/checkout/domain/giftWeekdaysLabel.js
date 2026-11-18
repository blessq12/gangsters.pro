const WEEKDAY_SHORT = {
    1: "пн",
    2: "вт",
    3: "ср",
    4: "чт",
    5: "пт",
    6: "сб",
    7: "вс",
};

/**
 * Клиентская подпись ограничения подарка по дням (courier).
 * @param {unknown} days
 * @returns {string}
 */
export function giftCourierWeekdaysLabel(days) {
    const list = Array.isArray(days)
        ? [...new Set(days.map((day) => Number(day)).filter((day) => day >= 1 && day <= 7))]
            .sort((a, b) => a - b)
        : [];

    if (list.length === 0) {
        return "Подарок при доставке сегодня недоступен";
    }

    if (list.join(",") === "1,2,3,4") {
        return "Подарок при доставке — пн–чт";
    }

    return `Подарок при доставке — ${list
        .map((day) => WEEKDAY_SHORT[day])
        .filter(Boolean)
        .join(", ")}`;
}
