import { defineStore } from "pinia";
import { fetchCatalogRequest } from "../api/catalogApi";
import { toCatalogStorageUrl } from "../utils/catalog/productMedia";

const CATALOG_STORAGE_KEY = "gangsters_catalog";

function extractWeightGrams(text) {
    if (!text || typeof text !== "string") return null;

    // Heuristics: ищем в описании/названии паттерны типа "300 г", "250г", "1.5 g".
    // В базе веса нет, поэтому делаем мягкое извлечение для UX.
    const raw = text.replace(",", ".").toLowerCase();
    const match = raw.match(/(\d+(?:\.\d+)?)\s*(г|гр|грамм|g)\b/iu);
    if (!match) return null;

    const grams = Number(match[1]);
    if (!Number.isFinite(grams) || grams <= 0) return null;

    // Округляем к целым, так как на UI показываем "г".
    return Math.round(grams);
}

function normalizeProduct(apiProduct) {
    if (!apiProduct || typeof apiProduct !== "object") {
        return null;
    }

    const id = apiProduct.id ?? null;
    if (!id) {
        return null;
    }

    let price = 0;
    if (Array.isArray(apiProduct.prices) && apiProduct.prices.length) {
        const defaultPrice =
            apiProduct.prices.find((p) => p && p.is_default) || apiProduct.prices[0];
        if (defaultPrice && typeof defaultPrice.amount !== "undefined") {
            const raw = Number(defaultPrice.amount) || 0;
            price = Math.round(raw / 100);
        }
    }

    let imageUrl = null;
    /** @type {{ url: string, width: number }[]} для srcset (thumb 300, medium 800, large 1200) */
    let imageSrcset = [];
    if (Array.isArray(apiProduct.images) && apiProduct.images.length) {
        const firstImage = apiProduct.images[0];
        if (firstImage && Array.isArray(firstImage.variants)) {
            const variants = firstImage.variants;
            const bySize = (size) =>
                variants.find(
                    (v) => v && typeof v === "object" && v.size === size && v.path,
                );
            const thumb = bySize("thumb");
            const medium = bySize("medium");
            const large = bySize("large");
            const order = [thumb, medium, large].filter(Boolean);
            if (order.length) {
                imageSrcset = order.map((v) => ({
                    url: toCatalogStorageUrl(v.path),
                    width: Number(v.width) || (v.size === "thumb" ? 300 : v.size === "medium" ? 800 : 1200),
                })).filter((e) => e.url);
                const fallback = order[order.length - 1];
                imageUrl = toCatalogStorageUrl(fallback.path);
            }
        }
    }

    const images = imageUrl ? [imageUrl] : [];

    const derivedWeight =
        extractWeightGrams(apiProduct.weight ?? apiProduct.description) ||
        extractWeightGrams(apiProduct.name);

    const nutrition =
        apiProduct.nutrition && typeof apiProduct.nutrition === "object"
            ? {
                  calories: Number(apiProduct.nutrition.calories) || 0,
                  proteins: Number(apiProduct.nutrition.proteins) || 0,
                  fats: Number(apiProduct.nutrition.fats) || 0,
                  carbs: Number(apiProduct.nutrition.carbs) || 0,
                  basis: apiProduct.nutrition.basis || "per_100g",
              }
            : null;

    return {
        id,
        name: apiProduct.name || "",
        price,
        weight: derivedWeight,
        images,
        imageSrcset,
        nutrition,
        raw: apiProduct,
    };
}

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

                if ("selectedProduct" in parsed) {
                    this.selectedProduct = parsed.selectedProduct ?? null;
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
                    selectedProduct: this.selectedProduct,
                }),
            );
        },
        setSelectedCategoryId(categoryId) {
            this.selectedCategoryId = categoryId ?? null;
            this.persist();
        },
        setSelectedProduct(product) {
            this.selectedProduct = product ?? null;
            this.persist();
        },
        setProductSearchQuery(query) {
            this.productSearchQuery =
                query == null ? "" : String(query);
        },

        async fetchCatalog() {
            this.loading = true;
            this.error = null;

            try {
                const payload = await fetchCatalogRequest();
                const rawCategories = Array.isArray(payload.categories)
                    ? payload.categories
                    : [];

                const mapped = rawCategories.map((item) => {
                    const category = item.category || {};
                    const products = Array.isArray(item.products)
                        ? item.products
                        : [];

                    return {
                        category: {
                            id: category.id ?? null,
                            name: category.name || "",
                            slug: category.slug || "",
                            sort_order: category.sort_order ?? null,
                            is_active: Boolean(category.is_active),
                            raw: category,
                        },
                        products: products
                            .map((p) => normalizeProduct(p))
                            .filter(Boolean),
                    };
                });
                this.categories = mapped.sort(
                    (a, b) =>
                        (a.category.sort_order ?? 0) - (b.category.sort_order ?? 0),
                );

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

