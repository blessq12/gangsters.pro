/** Нормализация рублей до сотых (как на бэке); отображение может скрывать ,00. */
export function roundRubles2(value) {
    const x = Number(value);
    if (!Number.isFinite(x)) {
        return 0;
    }
    return Math.round(x * 100) / 100;
}

/** Форматирование суммы в рублях для UI: копейки только если не ,00. */
export function formatMoneyRublesRu(value) {
    return new Intl.NumberFormat("ru-RU", {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(roundRubles2(value));
}
