import { defineStore } from "pinia";
import { DOMAIN_EVENTS, emitDomainEvent } from "../shared/domainEvents";
import { toggleFavoriteRequest, removeFavoriteRequest } from "../api/shoppingApi";

function normalizeProductSnapshot(product) {
    if (!product || typeof product !== "object") {
        return null;
    }

    return {
        id: product.id ?? null,
        name: product.name || "",
        price: Number(product.price) || 0,
        weight: product.weight ?? null,
    };
}

function normalizeFavoritesFromServer(list) {
    if (!Array.isArray(list)) return [];

    return list
        .map((item) => {
            if (!item || typeof item !== "object") return null;
            const productId = item.productId ?? item.productSnapshot?.id ?? null;
            if (!productId) return null;
            return {
                productId,
                productSnapshot: normalizeProductSnapshot({
                    id: productId,
                    ...(item.productSnapshot || {}),
                }),
            };
        })
        .filter(Boolean);
}

export const useFavoritesStore = defineStore("favorites", {
    state: () => ({
        items: [],
        loading: false,
        error: null,
    }),
    getters: {
        favorites(state) {
            return state.items;
        },
        count(state) {
            return state.items.length;
        },
        isFavorite: (state) => (id) => state.items.some((item) => item.productId === id),
    },
    actions: {
        initFromStorage() {
            /* миграция в shoppingBootstrap */
        },

        applyServerSnapshot(list) {
            this.items = normalizeFavoritesFromServer(list);
            emitDomainEvent(DOMAIN_EVENTS.FAVORITES_CHANGED, {
                items: this.items,
            });
        },

        async toggleFavorite(product) {
            const productId = typeof product === "object" ? product?.id : product;
            if (!productId) return;

            this.loading = true;
            this.error = null;
            try {
                const data = await toggleFavoriteRequest(productId);
                if (data?.favorites) {
                    this.applyServerSnapshot(data.favorites);
                }
            } catch (e) {
                console.error("toggleFavorite", e);
                this.error = e?.response?.data?.message || "Не удалось обновить избранное.";
                throw e;
            } finally {
                this.loading = false;
            }
        },

        async removeFavorite(productId) {
            this.loading = true;
            this.error = null;
            try {
                const data = await removeFavoriteRequest(productId);
                if (data?.favorites) {
                    this.applyServerSnapshot(data.favorites);
                }
            } catch (e) {
                console.error("removeFavorite", e);
                this.error = e?.response?.data?.message || "Не удалось обновить избранное.";
                throw e;
            } finally {
                this.loading = false;
            }
        },

        async clear() {
            const ids = this.items.map((i) => i.productId);
            for (const id of ids) {
                await this.removeFavorite(id);
            }
        },
    },
});
