import { defineStore } from "pinia";
import axios from "axios";

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
    if (Array.isArray(apiProduct.images) && apiProduct.images.length) {
        const firstImage = apiProduct.images[0];
        if (firstImage && Array.isArray(firstImage.variants)) {
            const variants = firstImage.variants;
            const bySize = (size) =>
                variants.find(
                    (v) => v && typeof v === "object" && v.size === size && v.path,
                );

            const medium = bySize("medium");
            const large = bySize("large");
            const thumb = bySize("thumb");
            const chosen = medium || large || thumb || variants[0];

            if (chosen && chosen.path) {
                imageUrl = chosen.path;
            }
        }
    }

    const images = imageUrl ? [imageUrl] : [];

    const consist =
        typeof apiProduct.description === "string" ? apiProduct.description : "";

    return {
        id,
        name: apiProduct.name || "",
        price,
        weight: null,
        consist,
        images,
        raw: apiProduct,
    };
}

export const useCatalogStore = defineStore("catalog", {
    state: () => ({
        categories: [],
        loading: false,
        error: null,
        selectedCategoryId: null,
        hasLoaded: false,
    }),
    getters: {
        flatProducts(state) {
            return state.categories.flatMap((entry) => entry.products || []);
        },
        filteredProducts(state) {
            if (!state.selectedCategoryId) {
                return this.flatProducts;
            }

            const entry = state.categories.find(
                (c) => c.category.id === state.selectedCategoryId,
            );
            if (!entry) {
                return [];
            }

            return entry.products || [];
        },
    },
    actions: {
        setSelectedCategoryId(categoryId) {
            this.selectedCategoryId = categoryId ?? null;
        },

        async fetchCatalog() {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.get("/api/catalog");
                const payload = response.data || {};
                const rawCategories = Array.isArray(payload.categories)
                    ? payload.categories
                    : [];

                this.categories = rawCategories.map((item) => {
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

