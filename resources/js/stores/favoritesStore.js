import { defineStore } from "pinia";
import { DOMAIN_EVENTS, emitDomainEvent } from "../shared/domainEvents";

const FAVORITES_STORAGE_KEY = "gangsters_favorites";
const CART_STORAGE_KEY = "gangsters_cart";

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

function normalizeFavorites(items) {
    if (!Array.isArray(items)) return [];

    return items
        .map((item) => {
            if (typeof item === "number" || typeof item === "string") {
                return {
                    productId: item,
                    productSnapshot: normalizeProductSnapshot({
                        id: item,
                        name: `Товар #${item}`,
                    }),
                };
            }
            if (!item || typeof item !== "object") return null;

            const productId = item.productId ?? item.productSnapshot?.id ?? item.id ?? null;
            if (!productId) return null;

            return {
                productId,
                productSnapshot: normalizeProductSnapshot({
                    id: productId,
                    ...(item.productSnapshot || item),
                }),
            };
        })
        .filter(Boolean);
}

export const useFavoritesStore = defineStore("favorites", {
    state: () => ({
        items: [],
    }),
    getters: {
        favorites(state) {
            return state.items;
        },
        count(state) {
            return state.items.length;
        },
        isFavorite: (state) => (id) =>
            state.items.some((item) => item.productId === id),
    },
    actions: {
        initFromStorage() {
            if (typeof window === "undefined") return;

            try {
                const raw = window.localStorage.getItem(FAVORITES_STORAGE_KEY);
                if (raw) {
                    const parsed = JSON.parse(raw);
                    if (parsed && typeof parsed === "object" && Array.isArray(parsed.items)) {
                        this.items = normalizeFavorites(parsed.items);
                        return;
                    }
                }

                // Миграция из gangsters_cart.favorites
                const cartRaw = window.localStorage.getItem(CART_STORAGE_KEY);
                if (cartRaw) {
                    const cartParsed = JSON.parse(cartRaw);
                    if (cartParsed && typeof cartParsed === "object" && Array.isArray(cartParsed.favorites)) {
                        this.items = normalizeFavorites(cartParsed.favorites);
                        this.persist();
                    }
                }

            } catch (e) {
                console.error("Failed to init favorites store from localStorage", e);
            }
        },
        persist() {
            if (typeof window === "undefined") return;
            window.localStorage.setItem(
                FAVORITES_STORAGE_KEY,
                JSON.stringify({
                    items: this.items,
                }),
            );
        },
        toggleFavorite(product) {
            const productId = typeof product === "object" ? product?.id : product;
            if (!productId) return;

            const existingIndex = this.items.findIndex((item) => item.productId === productId);
            if (existingIndex !== -1) {
                this.items.splice(existingIndex, 1);
            } else {
                this.items.push({
                    productId,
                    productSnapshot:
                        normalizeProductSnapshot(product) ||
                        normalizeProductSnapshot({
                            id: productId,
                            name: `Товар #${productId}`,
                        }),
                });
            }
            this.persist();
            emitDomainEvent(DOMAIN_EVENTS.FAVORITES_CHANGED, {
                items: this.items,
            });
        },
        removeFavorite(productId) {
            this.items = this.items.filter((item) => item.productId !== productId);
            this.persist();
            emitDomainEvent(DOMAIN_EVENTS.FAVORITES_CHANGED, {
                items: this.items,
            });
        },
        clear() {
            this.items = [];
            if (typeof window !== "undefined") {
                window.localStorage.removeItem(FAVORITES_STORAGE_KEY);
            }
            emitDomainEvent(DOMAIN_EVENTS.FAVORITES_CHANGED, {
                items: this.items,
            });
        },
    },
});

