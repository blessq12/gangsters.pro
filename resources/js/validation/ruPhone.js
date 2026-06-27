/**
 * Политика российского мобильного номера.
 * Канон: +7 (XXX) XXX-XX-XX (согласовано с App\Domain\Client\ValueObject\PhoneNumber).
 */

/**
 * Первая цифра после «+7 (» не может быть 7 или 8 — литерал +7 уже в маске.
 * Паттерн: цифры 0–6 и 9 (без 7 и 8).
 */
export const RU_PHONE_MASKA_TOKENS_ATTR = "F:[01234569]";

/** Те же токены для `new Mask({ tokens })` в JS. */
export const RU_PHONE_MASKA_TOKENS = Object.freeze({
    F: { pattern: /[01234569]/ },
});

/** Маска Maska для шаблона `data-maska` / опций Mask. */
export const RU_PHONE_MASKA_PATTERN = "+7 (F##) ###-##-##";

/** @type {Readonly<{ required: string; incomplete: string }>} */
export const RU_PHONE_MESSAGES = Object.freeze({
    required: "Введите номер телефона",
    incomplete: "Введите номер полностью — 10 цифр",
});

/**
 * Извлекает 10 цифр абонента из произвольной строки.
 * @param {string|null|undefined} raw
 * @returns {string}
 */
export function normalizeRuPhoneDigits(raw) {
    let digits = String(raw ?? "").replace(/\D/g, "");
    if (
        digits.length === 11 &&
        (digits[0] === "7" || digits[0] === "8")
    ) {
        digits = digits.slice(1);
    }
    while (
        digits.length > 0 &&
        digits.length < 11 &&
        (digits[0] === "7" || digits[0] === "8")
    ) {
        digits = digits.slice(1);
    }
    return digits.slice(0, 10);
}

/**
 * Канонический вид: +7 (XXX) XXX-XX-XX.
 * @param {string|null|undefined} raw
 * @returns {string} Пустая строка, если номер неполный.
 */
export function formatRuPhoneCanonical(raw) {
    const digits = normalizeRuPhoneDigits(raw);
    if (digits.length !== 10) {
        return "";
    }
    return `+7 (${digits.slice(0, 3)}) ${digits.slice(3, 6)}-${digits.slice(6, 8)}-${digits.slice(8, 10)}`;
}

/**
 * @param {string} digits — уже нормализованные цифры
 * @returns {boolean}
 */
export function isRuPhoneComplete(digits) {
    return normalizeRuPhoneDigits(digits).length === 10;
}

/**
 * @param {string|null|undefined} raw
 * @returns {{ ok: true, digits: string, formatted: string } | { ok: false, message: string }}
 */
export function validateRuPhoneForSubmit(raw) {
    const digits = normalizeRuPhoneDigits(raw);
    if (!digits) {
        return { ok: false, message: RU_PHONE_MESSAGES.required };
    }
    if (!isRuPhoneComplete(digits)) {
        return { ok: false, message: RU_PHONE_MESSAGES.incomplete };
    }
    return { ok: true, digits, formatted: formatRuPhoneCanonical(digits) };
}
