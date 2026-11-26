import { computed, ref, unref, watch } from "vue";

/**
 * @typedef {{
 *   key: string,
 *   id: number,
 *   kind: string,
 *   name: string,
 *   price_rubles?: number,
 *   ingredients?: string[],
 *   tech_card_text?: string,
 * }} MenuSearchItem
 */

/**
 * @typedef {{
 *   id: string,
 *   label: string,
 *   items: MenuSearchItem[],
 * }} MenuCategory
 */

/**
 * @param {MenuSearchItem} item
 * @param {string} q lower-case
 * @returns {boolean}
 */
function menuItemMatchesQuery(item, q) {
    if (!q) {
        return true;
    }
    if (
        String(item.name || "")
            .toLowerCase()
            .includes(q)
    ) {
        return true;
    }
    const ingredients = Array.isArray(item.ingredients) ? item.ingredients : [];
    for (const row of ingredients) {
        if (
            String(row || "")
                .toLowerCase()
                .includes(q)
        ) {
            return true;
        }
    }
    if (
        String(item.tech_card_text || "")
            .toLowerCase()
            .includes(q)
    ) {
        return true;
    }
    return false;
}

/**
 * Единый поиск/фильтр меню филиала (название + публичный состав + техкарта).
 *
 * @param {{
 *   categories: import('vue').Ref<MenuCategory[]>|import('vue').ComputedRef<MenuCategory[]>|MenuCategory[],
 *   dropdownLimit?: number,
 * }} options
 */
export function useBranchMenuSearch({ categories, dropdownLimit = 40 }) {
    const query = ref("");
    const categoryId = ref("");

    const sourceCategories = computed(() => {
        const rows = unref(categories);
        return Array.isArray(rows) ? rows : [];
    });

    const flatItems = computed(() => {
        /** @type {MenuSearchItem[]} */
        const flat = [];
        for (const category of sourceCategories.value) {
            for (const item of category.items || []) {
                flat.push(item);
            }
        }
        return flat;
    });

    const filteredCategories = computed(() => {
        const q = query.value.trim().toLowerCase();
        return sourceCategories.value
            .map((category) => {
                const items = (category.items || []).filter((item) =>
                    menuItemMatchesQuery(item, q),
                );
                return { ...category, items };
            })
            .filter((category) => category.items.length > 0);
    });

    const currentCategory = computed(() => {
        if (filteredCategories.value.length === 0) {
            return null;
        }
        const found = filteredCategories.value.find((row) => row.id === categoryId.value);
        return found || filteredCategories.value[0];
    });

    const resultItems = computed(() => currentCategory.value?.items || []);

    const dropdownHits = computed(() => {
        const q = query.value.trim().toLowerCase();
        const limit = Number(dropdownLimit) > 0 ? Number(dropdownLimit) : 40;
        if (!q) {
            return flatItems.value.slice(0, limit);
        }
        return flatItems.value.filter((item) => menuItemMatchesQuery(item, q)).slice(0, limit);
    });

    watch(
        filteredCategories,
        (rows) => {
            if (rows.length === 0) {
                categoryId.value = "";
                return;
            }
            if (!rows.some((row) => row.id === categoryId.value)) {
                categoryId.value = rows[0].id;
            }
        },
        { immediate: true },
    );

    function resetSearch() {
        query.value = "";
        categoryId.value = filteredCategories.value[0]?.id || "";
    }

    return {
        query,
        categoryId,
        flatItems,
        filteredCategories,
        currentCategory,
        resultItems,
        dropdownHits,
        resetSearch,
    };
}
