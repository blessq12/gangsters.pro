import { defineStore } from "pinia";

const CART_STORAGE_KEY = "gangsters_cart";
/** legacy: корзина раньше лежала в том же ключе, что и профиль */
const USER_LEGACY_KEY = "gangsters_user";

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

function normalizeCartItems(items) {
    if (!Array.isArray(items)) {
        return [];
    }

    return items
        .map((item) => {
            if (!item || typeof item !== "object") {
                return null;
            }

            const productId = item.productId ?? item.productSnapshot?.id ?? null;
            const qty = Number(item.qty) || 0;

            if (!productId || qty <= 0) {
                return null;
            }

            const snapshot = normalizeProductSnapshot({
                id: productId,
                ...(item.productSnapshot || {}),
            });

            return {
                productId,
                qty,
                productSnapshot: snapshot,
            };
        })
        .filter(Boolean);
}

function normalizeFavorites(items) {
    if (!Array.isArray(items)) {
        return [];
    }

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

            if (!item || typeof item !== "object") {
                return null;
            }

            const productId = item.productId ?? item.productSnapshot?.id ?? item.id ?? null;
            if (!productId) {
                return null;
            }

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

function stripCartFieldsFromUserPayload() {
    if (typeof window === "undefined") return;

    try {
        const raw = window.localStorage.getItem(USER_LEGACY_KEY);
        if (!raw) return;

        const parsed = JSON.parse(raw);
        if (!parsed || typeof parsed !== "object") return;

        if (!("cartItems" in parsed) && !("favorites" in parsed)) return;

        delete parsed.cartItems;
        delete parsed.favorites;
        window.localStorage.setItem(USER_LEGACY_KEY, JSON.stringify(parsed));
    } catch (e) {
        console.error("Failed to strip legacy cart fields from user storage", e);
    }
}

export const useCartStore = defineStore("cart", {
    state: () => ({
        cartItems: [],
        favorites: [],
    }),
    getters: {
        cartQuantityByProduct: (state) => (id) => {
            const item = state.cartItems.find((i) => i.productId === id);
            return item ? item.qty : 0;
        },
        isFavorite: (state) => (id) =>
            state.favorites.some((item) => item.productId === id),
        cartTotalItems(state) {
            return state.cartItems.reduce((sum, item) => sum + item.qty, 0);
        },
        cartTotalAmount(state) {
            return state.cartItems.reduce((sum, item) => {
                return sum + (Number(item.productSnapshot?.price) || 0) * item.qty;
            }, 0);
        },
    },
    actions: {
        initFromStorage() {
            if (typeof window === "undefined") return;

            try {
                const cartRaw = window.localStorage.getItem(CART_STORAGE_KEY);
                let loadedFromCartKey = false;

                if (cartRaw) {
                    const parsed = JSON.parse(cartRaw);
                    if (parsed && typeof parsed === "object") {
                        if (Array.isArray(parsed.cartItems)) {
                            this.cartItems = normalizeCartItems(parsed.cartItems);
                        }
                        if (Array.isArray(parsed.favorites)) {
                            this.favorites = normalizeFavorites(parsed.favorites);
                        }
                        loadedFromCartKey = true;
                    }
                }

                const userRaw = window.localStorage.getItem(USER_LEGACY_KEY);
                if (userRaw) {
                    const userParsed = JSON.parse(userRaw);
                    if (userParsed && typeof userParsed === "object") {
                        const hasLegacyCart =
                            Array.isArray(userParsed.cartItems) &&
                            userParsed.cartItems.length > 0;
                        const hasLegacyFav =
                            Array.isArray(userParsed.favorites) &&
                            userParsed.favorites.length > 0;

                        if (!loadedFromCartKey && (hasLegacyCart || hasLegacyFav)) {
                            if (Array.isArray(userParsed.cartItems)) {
                                this.cartItems = normalizeCartItems(userParsed.cartItems);
                            }
                            if (Array.isArray(userParsed.favorites)) {
                                this.favorites = normalizeFavorites(userParsed.favorites);
                            }
                            this.persist();
                        }

                        stripCartFieldsFromUserPayload();
                    }
                }
            } catch (e) {
                console.error("Failed to init cart store from localStorage", e);
            }
        },
        persist() {
            if (typeof window === "undefined") return;

            window.localStorage.setItem(
                CART_STORAGE_KEY,
                JSON.stringify({
                    cartItems: this.cartItems,
                    favorites: this.favorites,
                }),
            );
        },
        clear() {
            this.cartItems = [];
            this.favorites = [];
            if (typeof window !== "undefined") {
                window.localStorage.removeItem(CART_STORAGE_KEY);
            }
        },
        addToCart(product, qty = 1) {
            if (!product || !product.id) return;
            const id = product.id;
            const safeQty = Math.max(1, Number(qty) || 1);
            const snapshot = normalizeProductSnapshot(product);
            const existing = this.cartItems.find((i) => i.productId === id);
            if (existing) {
                existing.qty += safeQty;
                existing.productSnapshot = snapshot || existing.productSnapshot;
            } else {
                this.cartItems.push({
                    productId: id,
                    qty: safeQty,
                    productSnapshot: snapshot,
                });
            }
            this.persist();
        },
        incrementCart(productId) {
            const item = this.cartItems.find((i) => i.productId === productId);
            if (!item) return;
            item.qty += 1;
            this.persist();
        },
        decrementCart(productId) {
            const idx = this.cartItems.findIndex((i) => i.productId === productId);
            if (idx === -1) return;
            const item = this.cartItems[idx];
            item.qty -= 1;
            if (item.qty <= 0) {
                this.cartItems.splice(idx, 1);
            }
            this.persist();
        },
        removeFromCart(productId) {
            this.cartItems = this.cartItems.filter((item) => item.productId !== productId);
            this.persist();
        },
        toggleFavorite(product) {
            const productId =
                typeof product === "object" ? product?.id : product;

            if (!productId) return;

            const existingIndex = this.favorites.findIndex(
                (item) => item.productId === productId,
            );

            if (existingIndex !== -1) {
                this.favorites.splice(existingIndex, 1);
            } else {
                this.favorites.push({
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
        },
        removeFavorite(productId) {
            this.favorites = this.favorites.filter((item) => item.productId !== productId);
            this.persist();
        },
    },
});
