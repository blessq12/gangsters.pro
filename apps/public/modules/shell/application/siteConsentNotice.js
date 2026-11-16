/**
 * Согласие на аналитику/storage: флаг в localStorage на клиенте.
 */

export const SITE_CONSENT_NOTICE_STORAGE_KEY =
    "gangsters_cookie_consent_accepted_v1";

/**
 * @returns {boolean}
 */
export function hasAcceptedSiteConsentNotice() {
    if (typeof window === "undefined") {
        return true;
    }

    try {
        return (
            window.localStorage.getItem(SITE_CONSENT_NOTICE_STORAGE_KEY) === "1"
        );
    } catch {
        return false;
    }
}

export function acceptSiteConsentNotice() {
    if (typeof window === "undefined") {
        return;
    }

    try {
        window.localStorage.setItem(SITE_CONSENT_NOTICE_STORAGE_KEY, "1");
    } catch {
        // ignore quota / private mode
    }
}
