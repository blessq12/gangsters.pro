import { ref } from "vue";
import { getCatalog } from "../../api/catalog.js";
import {
    getBranchMenu,
    listBranchCategories,
    listBranchSets,
} from "../../api/company.js";
import { useApi } from "./useApi.js";

/**
 * Меню филиала для POS-панели (категории → items).
 */
export function useBranchMenu() {
    const menuApi = useApi();
    /** @type {import('vue').Ref<Array<Record<string, unknown>>>} */
    const categories = ref([]);

    /**
     * @param {number|null|undefined} branchId
     * @returns {Promise<void>}
     */
    async function loadMenu(branchId) {
        const id = Number(branchId);
        if (!Number.isFinite(id) || id < 1) {
            categories.value = [];
            return;
        }

        const [catalog, menu, cats, sets] = await menuApi.run(() => Promise.all([
            getCatalog(),
            getBranchMenu(id),
            listBranchCategories(id),
            listBranchSets(id),
        ]));

        const products = Array.isArray(catalog?.products) ? catalog.products : [];
        const productById = new Map(products.map((item) => [Number(item.id), item]));

        const menuById = new Map(
            (Array.isArray(menu?.items) ? menu.items : [])
                .map((item) => [Number(item.catalog_item_id), item]),
        );

        const setById = new Map(
            (Array.isArray(sets?.items) ? sets.items : Array.isArray(sets) ? sets : [])
                .map((item) => [Number(item.id), item]),
        );

        const categoryRows = Array.isArray(cats?.items) ? cats.items : [];
        /** @type {Array<Record<string, unknown>>} */
        const built = [];

        for (const category of categoryRows) {
            if (category?.is_active === false) {
                continue;
            }
            /** @type {Array<Record<string, unknown>>} */
            const items = [];
            for (const entry of (category.items || [])) {
                const entryId = Number(entry.entry_id);
                const kind = entry.entry_kind === "set" ? "set" : "product";
                if (kind === "set") {
                    const set = setById.get(entryId);
                    if (!set || set.is_available === false) {
                        continue;
                    }
                    items.push({
                        key: `set:${entryId}`,
                        id: entryId,
                        kind: "set",
                        name: String(set.name || `Набор #${entryId}`),
                        price_rubles: Number(set.price_rubles ?? Math.trunc(Number(set.price_kopecks || 0) / 100)),
                    });
                    continue;
                }
                const menuItem = menuById.get(entryId);
                if (!menuItem || menuItem.is_available === false) {
                    continue;
                }
                const product = productById.get(entryId);
                items.push({
                    key: `product:${entryId}`,
                    id: entryId,
                    kind: "product",
                    name: String(product?.name || `Товар #${entryId}`),
                    price_rubles: Number(menuItem.price_rubles ?? Math.trunc(Number(menuItem.price_kopecks || 0) / 100)),
                });
            }
            if (items.length === 0) {
                continue;
            }
            built.push({
                id: String(category.id),
                label: String(category.name || "Категория"),
                items,
            });
        }

        if (built.length === 0) {
            const flat = [];
            for (const [productId, menuItem] of menuById.entries()) {
                if (menuItem.is_available === false) {
                    continue;
                }
                const product = productById.get(productId);
                flat.push({
                    key: `product:${productId}`,
                    id: productId,
                    kind: "product",
                    name: String(product?.name || `Товар #${productId}`),
                    price_rubles: Number(menuItem.price_rubles ?? Math.trunc(Number(menuItem.price_kopecks || 0) / 100)),
                });
            }
            if (flat.length > 0) {
                built.push({ id: "all", label: "Меню", items: flat });
            }
        }

        categories.value = built;
    }

    function clearMenu() {
        categories.value = [];
    }

    return {
        menuApi,
        categories,
        loadMenu,
        clearMenu,
    };
}
