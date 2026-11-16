/**
 * @param {unknown} error
 * @returns {Record<string, string>}
 */
export function extractApiFieldErrors(error) {
    const raw = error?.response?.data?.errors;
    if (!raw || typeof raw !== "object") {
        return {};
    }

    /** @type {Record<string, string>} */
    const result = {};

    for (const [key, messages] of Object.entries(raw)) {
        if (Array.isArray(messages) && messages[0]) {
            result[key] = String(messages[0]);
            continue;
        }
        if (typeof messages === "string" && messages.trim() !== "") {
            result[key] = messages;
        }
    }

    return result;
}

/**
 * @param {import('./useFormFieldErrors').ReturnType<typeof import('./useFormFieldErrors').useFormFieldErrors>} fieldErrors
 * @param {unknown} error
 * @param {Record<string, string>} [fieldMap]
 * @returns {boolean}
 */
export function applyApiFieldErrors(fieldErrors, error, fieldMap = {}) {
    const extracted = extractApiFieldErrors(error);
    if (Object.keys(extracted).length === 0) {
        return false;
    }

    /** @type {Record<string, string>} */
    const mapped = {};
    for (const [apiKey, message] of Object.entries(extracted)) {
        const localKey = fieldMap[apiKey] ?? apiKey;
        mapped[localKey] = message;
    }

    fieldErrors.setErrors(mapped);
    return true;
}
