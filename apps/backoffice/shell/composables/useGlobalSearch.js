import { computed, ref, unref, watch } from "vue";
import { useRouter } from "vue-router";
import { providersForRole } from "../search/providers/index.js";

/**
 * Facade глобального поиска: debounce + parallel providers + группировка.
 *
 * @param {{
 *   role: import('vue').Ref<string>|string,
 *   branchId: import('vue').Ref<number|null>|number|null,
 *   debounceMs?: number,
 *   limitPerType?: number,
 * }} options
 */
export function useGlobalSearch(options) {
    const router = useRouter();
    const query = ref("");
    const open = ref(false);
    const loading = ref(false);
    const error = ref("");
    /** @type {import('vue').Ref<import('../search/types.js').GlobalSearchGroup[]>} */
    const groups = ref([]);

    const debounceMs = Number(options.debounceMs) > 0 ? Number(options.debounceMs) : 280;
    const limitPerType = Number(options.limitPerType) > 0 ? Number(options.limitPerType) : 8;

    let timer = /** @type {ReturnType<typeof setTimeout>|null} */ (null);
    let generation = 0;

    const providers = computed(() => providersForRole(unref(options.role)));

    const hasProviders = computed(() => providers.value.length > 0);

    const hasHits = computed(() => groups.value.some((group) => group.hits.length > 0));

    /**
     * @returns {import('../search/types.js').GlobalSearchContext}
     */
    function buildContext() {
        const rawBranch = unref(options.branchId);
        const branch = Number(rawBranch);
        return {
            role: String(unref(options.role) || ""),
            branchId: Number.isFinite(branch) && branch > 0 ? branch : null,
            limit: limitPerType,
        };
    }

    async function runSearch() {
        const q = query.value.trim();
        const current = ++generation;
        if (!q) {
            groups.value = [];
            loading.value = false;
            error.value = "";
            return;
        }

        const active = providers.value;
        if (active.length === 0) {
            groups.value = [];
            return;
        }

        loading.value = true;
        error.value = "";
        const ctx = buildContext();

        const settled = await Promise.allSettled(
            active.map(async (provider) => {
                const hits = await provider.search(q, ctx);
                return {
                    type: provider.type,
                    label: provider.label,
                    hits: Array.isArray(hits) ? hits : [],
                };
            }),
        );

        if (current !== generation) {
            return;
        }

        /** @type {import('../search/types.js').GlobalSearchGroup[]} */
        const next = [];
        let failed = 0;
        for (const result of settled) {
            if (result.status === "fulfilled") {
                if (result.value.hits.length > 0) {
                    next.push(result.value);
                }
            } else {
                failed += 1;
            }
        }
        groups.value = next;
        loading.value = false;
        if (failed > 0 && next.length === 0) {
            error.value = "Не удалось выполнить поиск";
        }
    }

    function scheduleSearch() {
        if (timer) {
            window.clearTimeout(timer);
        }
        timer = window.setTimeout(() => {
            timer = null;
            runSearch();
        }, debounceMs);
    }

    /**
     * @param {string} value
     */
    function setQuery(value) {
        query.value = String(value ?? "");
        scheduleSearch();
    }

    function openPanel() {
        open.value = true;
    }

    function closePanel() {
        open.value = false;
        query.value = "";
        groups.value = [];
        error.value = "";
        generation += 1;
        if (timer) {
            window.clearTimeout(timer);
            timer = null;
        }
        loading.value = false;
    }

    /**
     * @param {import('../search/types.js').GlobalSearchHit} hit
     */
    async function navigate(hit) {
        if (!hit?.route?.name) {
            return;
        }
        await router.push({
            name: hit.route.name,
            params: hit.route.params || {},
            query: hit.route.query || {},
        });
        closePanel();
    }

    watch(
        () => [unref(options.role), unref(options.branchId)],
        () => {
            if (open.value && query.value.trim()) {
                scheduleSearch();
            }
        },
    );

    return {
        query,
        open,
        loading,
        error,
        groups,
        hasProviders,
        hasHits,
        setQuery,
        openPanel,
        closePanel,
        runSearch,
        navigate,
    };
}
