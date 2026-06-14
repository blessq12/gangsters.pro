import { defineStore } from "pinia";
import { fetchCatalogTree } from "../services/catalog/catalogService";
import { mapApiError } from "../utils/api/mapApiError";

const CATALOG_STORAGE_KEY = "gangsters_catalog";
const DESKTOP_CARDS_PER_ROW_DEFAULT = 4;
const MOBILE_CARD_VIEW_MODE_DEFAULT = "grid";

function normalizeDesktopCardsPerRow(value) {
    return value === 3 || value === 4 ? value : DESKTOP_CARDS_PER_ROW_DEFAULT;
}

function normalizeMobileCardViewMode(value) {
    return value === "grid" || value === "horizontal"
        ? value
        : MOBILE_CARD_VIEW_MODE_DEFAULT;
}

export const useCatalogStore = defineStore("catalog", {
    state: () => ({
        categories: [],
        loading: false,
        error: null,
        selectedCategoryId: null,
        selectedProduct: null,
        hasLoaded: false,
        selectedTag: null,
        /** Поиск по названию в уже загруженном дереве (клиентский фильтр). */
        productSearchQuery: "",
        desktopCardsPerRow: DESKTOP_CARDS_PER_ROW_DEFAULT,
        mobileCardViewMode: MOBILE_CARD_VIEW_MODE_DEFAULT,
    }),
    getters: {
        flatProducts(state) {
            return state.categories.flatMap((entry) => entry.products || []);
        },
        filteredProducts(state) {
            if (state.selectedCategoryId == null || state.selectedCategoryId === "") {
                return this.flatProducts;
            }

            const selected = state.selectedCategoryId;
            const entry = state.categories.find((c) => {
                const id = c.category.id;
                const slug = c.category.slug;
                return (
                    id != null && Number(selected) === Number(id) ||
                    (slug && String(selected) === String(slug))
                );
            });
            if (!entry) {
                return [];
            }

            return entry.products || [];
        },
        /**
         * Лента меню: при вводе в поиск — все товары дерева по подстроке в name;
         * без поиска — как раньше (все или выбранная категория).
         */
        menuSections(state) {
            const q = this.productSearchQuery.trim().toLowerCase();
            const selectedTag = this.selectedTag;
            const byTag = (product) => {
                if (!selectedTag) return true;
                const tags = Array.isArray(product.tags) ? product.tags : [];
                return tags.some((tag) => String(tag?.code) === String(selectedTag));
            };
            const byQuery = (product) =>
                String(product?.name || "")
                    .toLowerCase()
                    .includes(q);

            const sourceCategories =
                state.selectedCategoryId == null || state.selectedCategoryId === ""
                    ? state.categories
                    : state.categories.filter((entry) => {
                          const id = entry.category?.id;
                          const slug = entry.category?.slug;
                          const selected = state.selectedCategoryId;
                          return (
                              (id != null && Number(selected) === Number(id)) ||
                              (slug && String(selected) === String(slug))
                          );
                      });

            const orderMap = new Map(
                state.categories.map((entry, index) => [
                    entry?.category?.id ?? entry?.category?.slug ?? null,
                    index,
                ]),
            );

            return sourceCategories
                .map((entry) => {
                    const base = Array.isArray(entry?.products) ? entry.products : [];
                    const products = base.filter((p) => {
                        if (!byTag(p)) return false;
                        if (q.length > 0) return byQuery(p);
                        return true;
                    });
                    return {
                        id: entry?.category?.id ?? entry?.category?.slug ?? null,
                        name: entry?.category?.name || "Без категории",
                        products,
                    };
                })
                .filter((section) => section.products.length > 0)
                .sort((a, b) => (orderMap.get(a.id) ?? 0) - (orderMap.get(b.id) ?? 0));
        },
        menuProducts() {
            return this.menuSections.flatMap((section) => section.products);
        },
        categoryTabs(state) {
            return state.categories.map((entry) => ({
                id: entry.category.id,
                name: entry.category.name,
                uri: entry.category.slug,
            }));
        },
        tagTabs() {
            const map = new Map();
            this.flatProducts.forEach((product) => {
                const tags = Array.isArray(product.tags) ? product.tags : [];
                tags.forEach((tag) => {
                    const code = String(tag?.code || "").trim();
                    if (!code || map.has(code)) return;

                    map.set(code, {
                        id: code,
                        name: String(tag?.label || code),
                    });
                });
            });

            return Array.from(map.values());
        },
    },
    actions: {
        initFromStorage() {
            if (typeof window === "undefined") return;

            try {
                const raw = window.localStorage.getItem(CATALOG_STORAGE_KEY);
                if (!raw) return;

                const parsed = JSON.parse(raw);
                if (!parsed || typeof parsed !== "object") return;

                if ("selectedCategoryId" in parsed) {
                    this.selectedCategoryId = parsed.selectedCategoryId ?? null;
                }
                if ("selectedTag" in parsed) {
                    this.selectedTag = parsed.selectedTag ?? null;
                }
                if ("desktopCardsPerRow" in parsed) {
                    this.desktopCardsPerRow = normalizeDesktopCardsPerRow(parsed.desktopCardsPerRow);
                }
                if ("mobileCardViewMode" in parsed) {
                    this.mobileCardViewMode = normalizeMobileCardViewMode(parsed.mobileCardViewMode);
                }

            } catch (e) {
                console.error("Failed to init catalog store from localStorage", e);
            }
        },
        persist() {
            if (typeof window === "undefined") return;

            window.localStorage.setItem(
                CATALOG_STORAGE_KEY,
                JSON.stringify({
                    selectedCategoryId: this.selectedCategoryId,
                    selectedTag: this.selectedTag,
                    desktopCardsPerRow: this.desktopCardsPerRow,
                    mobileCardViewMode: this.mobileCardViewMode,
                }),
            );
        },
        setSelectedCategoryId(categoryId) {
            this.selectedCategoryId = categoryId ?? null;
            this.persist();
        },
        setSelectedTag(tagCode) {
            this.selectedTag = tagCode ?? null;
            this.persist();
        },
        setSelectedProduct(product) {
            this.selectedProduct = product ?? null;
        },
        setProductSearchQuery(query) {
            this.productSearchQuery =
                query == null ? "" : String(query);
        },
        setDesktopCardsPerRow(value) {
            this.desktopCardsPerRow = normalizeDesktopCardsPerRow(value);
            this.persist();
        },
        setMobileCardViewMode(value) {
            this.mobileCardViewMode = normalizeMobileCardViewMode(value);
            this.persist();
        },

        /**
         * Сбрасывает category/tag из localStorage, если после загрузки дерева
         * они больше не существуют (иначе menuProducts пустой при живых данных).
         */
        sanitizePersistedFiltersAfterLoad() {
            const categories = this.categories;
            if (!Array.isArray(categories) || categories.length === 0) {
                return;
            }

            let changed = false;
            const sel = this.selectedCategoryId;
            if (sel != null && sel !== "") {
                const found = categories.some((entry) => {
                    const id = entry.category?.id;
                    const slug = entry.category?.slug;
                    return (
                        (id != null && Number(sel) === Number(id)) ||
                        (slug && String(sel) === String(slug))
                    );
                });
                if (!found) {
                    this.selectedCategoryId = null;
                    changed = true;
                }
            }

            const tag = this.selectedTag;
            if (tag) {
                const flat = categories.flatMap((e) => e.products || []);
                const tagOk = flat.some((product) => {
                    const tags = Array.isArray(product.tags) ? product.tags : [];
                    return tags.some((t) => String(t?.code) === String(tag));
                });
                if (!tagOk) {
                    this.selectedTag = null;
                    changed = true;
                }
            }

            if (changed) {
                this.persist();
            }
        },

        async fetchAll() {
            this.loading = true;
            this.error = null;

            try {
                this.categories = await fetchCatalogTree();
                this.sanitizePersistedFiltersAfterLoad();

                this.hasLoaded = true;
            } catch (e) {
                console.error("Failed to fetch catalog", e);
                this.error = mapApiError(
                    e,
                    "Не удалось загрузить каталог. Попробуйте обновить страницу.",
                );
            } finally {
                this.loading = false;
            }
        },
    },
});

