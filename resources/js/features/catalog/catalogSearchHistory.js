const CATALOG_SEARCH_HISTORY_KEY = "gangsters_catalog_search_history_v1";
const MAX_HISTORY_ENTRIES = 8;
const MIN_QUERY_LENGTH = 2;

/**
 * @returns {string[]}
 */
export function readCatalogSearchHistory() {
    if (typeof window === "undefined") {
        return [];
    }

    try {
        const raw = window.localStorage.getItem(CATALOG_SEARCH_HISTORY_KEY);
        if (!raw) {
            return [];
        }

        const parsed = JSON.parse(raw);
        if (!Array.isArray(parsed)) {
            return [];
        }

        return parsed
            .map((entry) => String(entry || "").trim())
            .filter((entry) => entry.length >= MIN_QUERY_LENGTH)
            .slice(0, MAX_HISTORY_ENTRIES);
    } catch {
        return [];
    }
}

/**
 * @param {string} query
 * @returns {string[]}
 */
export function rememberCatalogSearchQuery(query) {
    const normalized = String(query ?? "").trim();
    if (normalized.length < MIN_QUERY_LENGTH) {
        return readCatalogSearchHistory();
    }

    const previous = readCatalogSearchHistory().filter(
        (entry) => entry.toLowerCase() !== normalized.toLowerCase(),
    );
    const next = [normalized, ...previous].slice(0, MAX_HISTORY_ENTRIES);

    if (typeof window !== "undefined") {
        window.localStorage.setItem(CATALOG_SEARCH_HISTORY_KEY, JSON.stringify(next));
    }

    return next;
}
