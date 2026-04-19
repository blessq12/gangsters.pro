import { defineStore } from "pinia";
import { DOMAIN_EVENTS, emitDomainEvent } from "../shared/domainEvents";
import { roundRubles2 } from "../utils/moneyFormat";
import {
    upsertCartLineRequest,
    removeCartLineRequest,
    clearCartRequest,
    recalculateCartRequest,
} from "../api/shoppingApi";

function normalizeProductSnapshot(product) {
    if (!product || typeof product !== "object") {
        return null;
    }

    return {
        id: product.id ?? null,
        name: product.name || "",
        price: roundRubles2(Number(product.price) || 0),
        weight: product.weight ?? null,
    };
}

function normalizeCartItemsFromServer(items) {
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

export const useCartStore = defineStore("cart", {
    state: () => ({
        cartItems: [],
        loading: false,
        error: null,
    }),
    getters: {
        cartQuantityByProduct: (state) => (id) => {
            const item = state.cartItems.find((i) => i.productId === id);
            return item ? item.qty : 0;
        },
        cartTotalItems(state) {
            return state.cartItems.reduce((sum, item) => sum + item.qty, 0);
        },
        cartTotalAmount(state) {
            const raw = state.cartItems.reduce((sum, item) => {
                return sum + (Number(item.productSnapshot?.price) || 0) * item.qty;
            }, 0);
            return roundRubles2(raw);
        },
    },
    actions: {
        initFromStorage() {
            /* миграция в bootstrapShoppingFromApi; локальный кэш корзины не используем */
        },

        /**
         * @param {object} cart — фрагмент ответа /api/shopping/state (поле cart)
         */
        applyServerSnapshot(cart) {
            this.cartItems = normalizeCartItemsFromServer(cart?.items ?? []);
            emitDomainEvent(DOMAIN_EVENTS.CART_CHANGED, { items: this.cartItems });
        },

        _applyStateData(data) {
            if (data && typeof data === "object" && data.cart) {
                this.applyServerSnapshot(data.cart);
            }
        },

        async addToCart(product, qty = 1) {
            if (!product || !product.id) return;
            const id = product.id;
            const add = Math.max(1, Number(qty) || 1);
            const existing = this.cartItems.find((i) => i.productId === id);
            const nextQty = (existing ? existing.qty : 0) + add;
            await this._upsertLine(id, nextQty);
        },

        async incrementCart(productId) {
            const item = this.cartItems.find((i) => i.productId === productId);
            if (!item) return;
            await this._upsertLine(productId, item.qty + 1);
        },

        async decrementCart(productId) {
            const item = this.cartItems.find((i) => i.productId === productId);
            if (!item) return;
            const next = item.qty - 1;
            if (next <= 0) {
                await this.removeFromCart(productId);
            } else {
                await this._upsertLine(productId, next);
            }
        },

        async removeFromCart(productId) {
            this.loading = true;
            this.error = null;
            try {
                const data = await removeCartLineRequest(productId);
                this._applyStateData(data);
            } catch (e) {
                console.error("removeFromCart", e);
                this.error = e?.response?.data?.message || "Не удалось обновить корзину.";
                throw e;
            } finally {
                this.loading = false;
            }
        },

        async clear() {
            this.loading = true;
            this.error = null;
            try {
                const data = await clearCartRequest();
                this._applyStateData(data);
                emitDomainEvent(DOMAIN_EVENTS.CART_CLEARED);
            } catch (e) {
                console.error("clear cart", e);
                this.error = e?.response?.data?.message || "Не удалось очистить корзину.";
                throw e;
            } finally {
                this.loading = false;
            }
        },

        async recalculateFromServer() {
            this.loading = true;
            this.error = null;
            try {
                const data = await recalculateCartRequest();
                this._applyStateData(data);
            } catch (e) {
                console.error("recalculate cart", e);
                this.error = e?.response?.data?.message || "Не удалось пересчитать корзину.";
                throw e;
            } finally {
                this.loading = false;
            }
        },

        async _upsertLine(productId, quantity) {
            this.loading = true;
            this.error = null;
            try {
                const data = await upsertCartLineRequest({
                    product_id: Number(productId),
                    quantity: Number(quantity),
                });
                this._applyStateData(data);
            } catch (e) {
                console.error("upsert cart line", e);
                this.error = e?.response?.data?.message || "Не удалось обновить корзину.";
                throw e;
            } finally {
                this.loading = false;
            }
        },
    },
});
