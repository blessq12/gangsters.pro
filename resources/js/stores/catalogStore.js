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

    function toPublicUrl(path) {
        if (!path || typeof path !== "string") return null;
        let url = String(path);
        if (url.startsWith("products/") || url.startsWith("uploads/")) {
            url = `/storage/${url}`;
        } else if (!url.startsWith("/")) {
            url = `/storage/${url.replace(/^\/+/, "")}`;
        }
        return url;
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
                    url: toPublicUrl(v.path),
                    width: Number(v.width) || (v.size === "thumb" ? 300 : v.size === "medium" ? 800 : 1200),
                })).filter((e) => e.url);
                const fallback = order[order.length - 1];
                imageUrl = toPublicUrl(fallback.path);
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
        imageSrcset,
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

