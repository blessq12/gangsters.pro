import { ref } from "vue";
import { getCatalog } from "../../api/catalog.js";
import { getBranchMenu, listBranchCategories, listBranchSets } from "../../api/company.js";
import { useApi } from "./useApi.js";

/**
 * @typedef {{
 *   key: string,
 *   id: number,
 *   kind: 'product'|'set',
 *   name: string,
 *   price_rubles: number,
 *   ingredients: string[],
 *   tech_card_text: string,
 *   description: string,
 *   weight: number|null,
 *   image_url: string,
 *   image_focus_x: number|null,
 *   image_focus_y: number|null,
 * }} MenuItem
 */

/**
 * Меню филиала для POS-панели и поиска (категории → items).
 */
export function useBranchMenu() {
    const menuApi = useApi();
    /** @type {import('vue').Ref<Array<{ id: string, label: string, items: MenuItem[] }>>} */
    const categories = ref([]);

    /**
     * @param {Record<string, unknown>|null|undefined} product
     * @returns {string[]}
     */
    function productIngredients(product) {
        return Array.isArray(product?.ingredients)
            ? product.ingredients.map((row) => String(row))
            : [];
    }

    /**
     * @param {Record<string, unknown>|null|undefined} product
     * @returns {string}
     */
    function productTechCardText(product) {
        return String(product?.tech_card_search || "").trim();
    }

    /**
     * @param {number} entryId
     * @param {Map<number, Record<string, unknown>>} productById
     * @param {Map<number, Record<string, unknown>>} menuById
     * @returns {MenuItem|null}
     */
    function buildProductItem(entryId, productById, menuById) {
        const menuItem = menuById.get(entryId);
        if (!menuItem || menuItem.is_available === false) {
            return null;
        }
        const product = productById.get(entryId);
        const weightRaw = product?.weight;
        const weightNum = weightRaw == null || weightRaw === "" ? null : Number(weightRaw);
        const focusX = product?.image_focus_x;
        const focusY = product?.image_focus_y;
        return {
            key: `product:${entryId}`,
            id: entryId,
            kind: "product",
            name: String(product?.name || `Товар #${entryId}`),
            price_rubles: Number(
                menuItem.price_rubles ?? Math.trunc(Number(menuItem.price_kopecks || 0) / 100),
            ),
            ingredients: productIngredients(product),
            tech_card_text: productTechCardText(product),
            description: String(product?.description || "").trim(),
            weight: Number.isFinite(weightNum) ? weightNum : null,
            image_url: String(product?.image_url || "").trim(),
            image_focus_x: focusX == null || focusX === "" ? null : Number(focusX),
            image_focus_y: focusY == null || focusY === "" ? null : Number(focusY),
        };
    }

    /**
     * @param {number} entryId
     * @param {Map<number, Record<string, unknown>>} setById
     * @returns {MenuItem|null}
     */
    function buildSetItem(entryId, setById) {
        const set = setById.get(entryId);
        if (!set || set.is_available === false) {
            return null;
        }
        return {
            key: `set:${entryId}`,
            id: entryId,
            kind: "set",
            name: String(set.name || `Набор #${entryId}`),
            price_rubles: Number(
                set.price_rubles ?? Math.trunc(Number(set.price_kopecks || 0) / 100),
            ),
            ingredients: [],
            tech_card_text: "",
            description: String(set.description || "").trim(),
            weight: null,
            image_url: "",
            image_focus_x: null,
            image_focus_y: null,
        };
    }

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

        const [catalog, menu, cats, sets] = await menuApi.run(() =>
            Promise.all([
                getCatalog(),
                getBranchMenu(id),
                listBranchCategories(id),
                listBranchSets(id),
            ]),
        );

        const products = Array.isArray(catalog?.products) ? catalog.products : [];
        const productById = new Map(products.map((item) => [Number(item.id), item]));

        const menuById = new Map(
            (Array.isArray(menu?.items) ? menu.items : []).map((item) => [
                Number(item.catalog_item_id),
                item,
            ]),
        );

        const setById = new Map(
            (Array.isArray(sets?.items) ? sets.items : Array.isArray(sets) ? sets : []).map(
                (item) => [Number(item.id), item],
            ),
        );

        const categoryRows = Array.isArray(cats?.items) ? cats.items : [];
        /** @type {Array<{ id: string, label: string, items: MenuItem[] }>} */
        const built = [];

        for (const category of categoryRows) {
            if (category?.is_active === false) {
                continue;
            }
            /** @type {MenuItem[]} */
            const items = [];
            for (const entry of category.items || []) {
                const entryId = Number(entry.entry_id);
                const kind = entry.entry_kind === "set" ? "set" : "product";
                if (kind === "set") {
                    const setItem = buildSetItem(entryId, setById);
                    if (setItem) {
                        items.push(setItem);
                    }
                    continue;
                }
                const productItem = buildProductItem(entryId, productById, menuById);
                if (productItem) {
                    items.push(productItem);
                }
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
            /** @type {MenuItem[]} */
            const flat = [];
            for (const [productId] of menuById.entries()) {
                const productItem = buildProductItem(productId, productById, menuById);
                if (productItem) {
                    flat.push(productItem);
                }
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
