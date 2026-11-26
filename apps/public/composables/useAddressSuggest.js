import { onBeforeUnmount, ref, unref } from "vue";
import { suggestAddressFromDaData } from "../geo/dadataSuggest.js";

/**
 * Debounce + токен запросов к ДаДата. Город филиала обязателен.
 * Синхронно с apps/backoffice/shell/composables/useAddressSuggest.js
 *
 * @param {{
 *   city: import('vue').Ref<string>|(() => string)|string,
 *   debounceMs?: number,
 *   limit?: number,
 *   onError?: (err: Error) => void,
 * }} options
 */
export function useAddressSuggest(options) {
    const debounceMs = Number.isFinite(Number(options.debounceMs))
        ? Number(options.debounceMs)
        : 280;
    const limit = Number.isFinite(Number(options.limit)) ? Number(options.limit) : 8;
    const onError = typeof options.onError === "function" ? options.onError : null;

    const searchQuery = ref("");
    const hits = ref(/** @type {Array<Record<string, unknown>>} */ ([]));
    const open = ref(false);
    const searching = ref(false);

    /** @type {ReturnType<typeof setTimeout>|null} */
    let timer = null;
    let requestToken = 0;
    let suppressSuggest = false;
    let errorNotified = false;

    function resolveCity() {
        const source = options.city;
        if (typeof source === "function") {
            return String(source() ?? "").trim();
        }
        return String(unref(source) ?? "").trim();
    }

    function clearTimer() {
        if (timer) {
            clearTimeout(timer);
            timer = null;
        }
    }

    /**
     * @param {string} [label]
     */
    function pickAndClose(label) {
        clearTimer();
        requestToken += 1;
        searching.value = false;
        open.value = false;
        hits.value = [];
        if (label !== undefined) {
            suppressSuggest = true;
            searchQuery.value = String(label ?? "");
        }
    }

    function reset() {
        clearTimer();
        requestToken += 1;
        searching.value = false;
        open.value = false;
        hits.value = [];
        suppressSuggest = false;
        errorNotified = false;
        searchQuery.value = "";
    }

    /**
     * @param {string} query
     */
    async function runSuggest(query) {
        const text = String(query ?? "").trim();
        const city = resolveCity();
        if (text.length < 2 || !city) {
            hits.value = [];
            open.value = false;
            searching.value = false;
            return;
        }

        const token = ++requestToken;
        searching.value = true;
        try {
            const list = await suggestAddressFromDaData({
                query: text,
                city,
                limit,
            });
            if (token !== requestToken) {
                return;
            }
            errorNotified = false;
            hits.value = list;
            open.value = list.length > 0;
        } catch (err) {
            if (token !== requestToken) {
                return;
            }
            hits.value = [];
            open.value = false;
            if (!errorNotified && onError) {
                errorNotified = true;
                onError(err instanceof Error ? err : new Error(String(err)));
            }
        } finally {
            if (token === requestToken) {
                searching.value = false;
            }
        }
    }

    /**
     * @param {string} query
     */
    function scheduleSuggest(query) {
        if (suppressSuggest) {
            suppressSuggest = false;
            clearTimer();
            return;
        }
        clearTimer();
        timer = setTimeout(() => {
            timer = null;
            runSuggest(query);
        }, debounceMs);
    }

    /**
     * @param {string} label
     */
    function setSearchLabel(label) {
        suppressSuggest = true;
        searchQuery.value = String(label ?? "");
    }

    onBeforeUnmount(() => {
        clearTimer();
        requestToken += 1;
    });

    return {
        searchQuery,
        hits,
        open,
        searching,
        scheduleSuggest,
        runSuggest,
        pickAndClose,
        reset,
        setSearchLabel,
        resolveCity,
    };
}
