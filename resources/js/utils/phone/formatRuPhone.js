/**
 * Человекочитаемый российский номер из произвольной строки.
 * @param {string|null|undefined} raw
 * @returns {string}
 */
export function formatRuPhone(raw) {
    if (!raw) return "";
    const digits = String(raw).replace(/\D/g, "");
    const tail = digits.slice(-10);
    if (tail.length !== 10) {
        return String(raw);
    }
    const part1 = tail.slice(0, 3);
    const part2 = tail.slice(3, 6);
    const part3 = tail.slice(6, 8);
    const part4 = tail.slice(8, 10);
    return `+7 (${part1}) ${part2}-${part3}-${part4}`;
}

/**
 * Для ссылки tel:
 */
export function phoneToTelHref(raw) {
    if (!raw) return null;
    const digits = String(raw).replace(/\D/g, "");
    if (!digits.length) return null;
    return digits.startsWith("7") && digits.length === 11
        ? `tel:+${digits}`
        : digits.length === 10
          ? `tel:+7${digits}`
          : `tel:+${digits}`;
}
