import {
    formatRuPhoneCanonical,
    normalizeRuPhoneDigits,
} from "../../validation/ruPhone";

/**
 * Человекочитаемый российский номер из произвольной строки.
 * @param {string|null|undefined} raw
 * @returns {string}
 */
export function formatRuPhone(raw) {
    const formatted = formatRuPhoneCanonical(raw);
    if (formatted) {
        return formatted;
    }
    return raw ? String(raw) : "";
}

/**
 * Для ссылки tel:
 */
export function phoneToTelHref(raw) {
    if (!raw) return null;
    const digits = normalizeRuPhoneDigits(raw);
    if (digits.length !== 10) {
        const loose = String(raw).replace(/\D/g, "");
        if (!loose.length) return null;
        return loose.startsWith("7") && loose.length === 11
            ? `tel:+${loose}`
            : `tel:+${loose}`;
    }
    return `tel:+7${digits}`;
}
