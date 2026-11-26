import { computed, ref, watch } from "vue";

/**
 * Клиентский поиск + пагинация по уже загруженному списку.
 * @param {{
 *   perPage?: number,
 *   getHaystack?: (row: any) => string,
 * }} [options]
 */
export function useClientList(options = {}) {
    const perPage = Number(options.perPage) > 0 ? Number(options.perPage) : 20;
    const getHaystack =
        typeof options.getHaystack === "function"
            ? options.getHaystack
            : (row) => String(row?.name ?? row?.label ?? row?.code ?? "");

    const allItems = ref([]);
    const searchQuery = ref("");
    const page = ref(1);

    const normalizedQuery = computed(() => searchQuery.value.trim().toLowerCase());

    const filteredItems = computed(() => {
        const q = normalizedQuery.value;
        if (q === "") {
            return allItems.value;
        }
        return allItems.value.filter((row) => getHaystack(row).toLowerCase().includes(q));
    });

    const total = computed(() => filteredItems.value.length);

    const pageItems = computed(() => {
        const start = (page.value - 1) * perPage;
        return filteredItems.value.slice(start, start + perPage);
    });

    function setItems(rows) {
        allItems.value = Array.isArray(rows) ? rows : [];
        page.value = 1;
    }

    function resetPage() {
        page.value = 1;
    }

    watch(searchQuery, () => {
        page.value = 1;
    });

    watch(total, (value) => {
        const maxPage = Math.max(1, Math.ceil(value / perPage) || 1);
        if (page.value > maxPage) {
            page.value = maxPage;
        }
    });

    return {
        allItems,
        searchQuery,
        page,
        perPage,
        total,
        filteredItems,
        pageItems,
        setItems,
        resetPage,
    };
}
