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
            if (q.length > 0) {
                return this.flatProducts.filter((p) =>
                    String(p.name || "")
                        .toLowerCase()
                        .includes(q),
                );
            }
            return this.filteredProducts;
        },
        categoryTabs(state) {
            return state.categories.map((entry) => ({
                id: entry.category.id,
                name: entry.category.name,
                uri: entry.category.slug,
            }));
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
                }),
            );
        },
        setSelectedCategoryId(categoryId) {
            this.selectedCategoryId = categoryId ?? null;
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

