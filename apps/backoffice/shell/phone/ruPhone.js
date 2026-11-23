/** Политика российского мобильного: 10 цифр абонента, канон +7 (XXX) XXX-XX-XX. */

/**
 * @param {string|null|undefined} raw
 * @returns {string}
 */
export function normalizeRuPhoneDigits(raw) {
    let digits = String(raw ?? "").replace(/\D/g, "");
    if (digits.length === 11 && (digits[0] === "7" || digits[0] === "8")) {
        digits = digits.slice(1);
    }
    while (digits.length > 0 && digits.length < 11 && (digits[0] === "7" || digits[0] === "8")) {
        digits = digits.slice(1);
    }
    return digits.slice(0, 10);
}

/**
 * @param {string|null|undefined} raw
 * @returns {string}
 */
export function formatRuPhoneCanonical(raw) {
    const digits = normalizeRuPhoneDigits(raw);
    if (digits.length !== 10) {
        return "";
    }
    return `+7 (${digits.slice(0, 3)}) ${digits.slice(3, 6)}-${digits.slice(6, 8)}-${digits.slice(8, 10)}`;
}

/**
 * @param {string|null|undefined} raw
 * @returns {boolean}
 */
export function isRuPhoneComplete(raw) {
    return normalizeRuPhoneDigits(raw).length === 10;
}
