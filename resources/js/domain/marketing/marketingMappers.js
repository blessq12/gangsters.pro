function safeTrim(value) {
    if (value == null) return "";
    if (typeof value === "string") return value.trim();
    return String(value).trim();
}

function nullableString(value) {
    const trimmed = safeTrim(value);
    return trimmed !== "" ? trimmed : null;
}

/**
 * @param {unknown} apiBanner
 * @returns {object|null}
 */
export function normalizeMarketingBanner(apiBanner) {
    if (!apiBanner || typeof apiBanner !== "object") {
        return null;
    }

    const imageDesktop = nullableString(apiBanner.image_desktop);
    const imageMobile = nullableString(apiBanner.image_mobile);

    if (!imageDesktop && !imageMobile) {
        return null;
    }

    return {
        id: apiBanner.id ?? null,
        image_desktop: imageDesktop,
        image_mobile: imageMobile,
    };
}

/**
 * @param {unknown} apiPromotion
 * @returns {object|null}
 */
export function normalizeMarketingPromotion(apiPromotion) {
    if (!apiPromotion || typeof apiPromotion !== "object") {
        return null;
    }

    const title = safeTrim(apiPromotion.title);
    if (!title) {
        return null;
    }

    const body =
        typeof apiPromotion.body === "string" && apiPromotion.body.trim() !== ""
            ? apiPromotion.body
            : null;

    return {
        id: apiPromotion.id ?? null,
        title,
        image: nullableString(apiPromotion.image),
        body,
        description: nullableString(apiPromotion.description),
    };
}
