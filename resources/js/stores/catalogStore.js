import { defineStore } from "pinia";
import { fetchCatalogTree } from "../services/catalog/catalogService";

const CATALOG_STORAGE_KEY = "gangsters_catalog";

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
        menuProducts() {
            const q = this.productSearchQuery.trim().toLowerCase();
            const selectedTag = this.selectedTag;
            const byTag = (product) => {
                if (!selectedTag) return true;
                const tags = Array.isArray(product.tags) ? product.tags : [];
                return tags.some((tag) => String(tag?.code) === String(selectedTag));
            };

            if (q.length > 0) {
                return this.flatProducts.filter((p) => {
                    const byQuery = String(p.name || "")
                        .toLowerCase()
                        .includes(q);

                    return byQuery && byTag(p);
                });
            }
            return this.filteredProducts.filter(byTag);
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

        async fetchCatalog() {
            this.loading = true;
            this.error = null;

            try {
                this.categories = await fetchCatalogTree();

                this.hasLoaded = true;
            } catch (e) {
                console.error("Failed to fetch catalog", e);
                this.error =
                    e?.response?.data?.message ||
                    "Не удалось загрузить каталог. Попробуйте обновить страницу.";
            } finally {
                this.loading = false;
            }
        },
    },
});

