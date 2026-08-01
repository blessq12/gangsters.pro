const BOOTSTRAP_CACHE_KEY = "gangsters_app_bootstrap_v1";
const BOOTSTRAP_CACHE_MAX_AGE_MS = 7 * 24 * 60 * 60 * 1000;

/**
 * @returns {{ version: string, critical: object, deferred: object|null, savedAt: number } | null}
 */
export function readBootstrapCache() {
    if (typeof window === "undefined") {
        return null;
    }

    try {
        const raw = window.localStorage.getItem(BOOTSTRAP_CACHE_KEY);
        if (!raw) {
            return null;
        }

        const parsed = JSON.parse(raw);
        if (!parsed || typeof parsed !== "object" || !parsed.version || !parsed.critical) {
            return null;
        }

        const savedAt = Number(parsed.savedAt) || 0;
        if (savedAt > 0 && Date.now() - savedAt > BOOTSTRAP_CACHE_MAX_AGE_MS) {
            return null;
        }

        return parsed;
    } catch {
        return null;
    }
}

/**
 * @param {{ version: string, critical: object, deferred?: object|null }} payload
 */
export function writeBootstrapCache(payload) {
    if (typeof window === "undefined") {
        return;
    }

    try {
        window.localStorage.setItem(
            BOOTSTRAP_CACHE_KEY,
            JSON.stringify({
                version: payload.version,
                critical: payload.critical,
                deferred: payload.deferred ?? null,
                savedAt: Date.now(),
            }),
        );
    } catch {
        // localStorage переполнен или недоступен
    }
}

export function clearBootstrapCache() {
    if (typeof window === "undefined") {
        return;
    }

    try {
        window.localStorage.removeItem(BOOTSTRAP_CACHE_KEY);
    } catch {
        // ignore
    }
}
