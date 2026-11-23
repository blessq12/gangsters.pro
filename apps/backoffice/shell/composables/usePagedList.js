import { computed, ref } from "vue";

/**
 * Единый state для server-side листингов бэкофиса.
 * @param {{ perPage?: number }} [options]
 */
export function usePagedList(options = {}) {
    const perPage = Number(options.perPage) > 0 ? Number(options.perPage) : 20;
    const page = ref(1);
    const total = ref(0);
    const items = ref([]);

    const offset = computed(() => (page.value - 1) * perPage);

    function resetPage() {
        page.value = 1;
    }

    /**
     * @param {{ items?: unknown[], total?: number }|null|undefined} payload
     * @param {(row: unknown) => unknown} [mapItem]
     */
    function applyPayload(payload, mapItem) {
        total.value = Number(payload?.total ?? 0);
        const rows = Array.isArray(payload?.items) ? payload.items : [];
        items.value = typeof mapItem === "function" ? rows.map(mapItem) : rows;
    }

    function clearItems() {
        items.value = [];
        total.value = 0;
    }

    return {
        page,
        perPage,
        total,
        items,
        offset,
        resetPage,
        applyPayload,
        clearItems,
    };
}
