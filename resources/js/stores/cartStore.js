import { defineStore } from "pinia";
import { DOMAIN_EVENTS, emitDomainEvent } from "../shared/domainEvents";
import { roundRubles2 } from "../utils/moneyFormat";
import {
    upsertCartLineRequest,
    removeCartLineRequest,
    clearCartRequest,
    recalculateCartRequest,
} from "../api/shoppingApi";
import {
    applyCartAndDeliveryFromState,
    applyShoppingMetaFromState,
} from "../features/shopping/applyShoppingState";

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

            const lineKey = String(item.line_key || `user:${productId}`);
            const origin = String(item.origin || "user");
            const isSystem = origin === "system";
            const pricing =
                item.pricing && typeof item.pricing === "object"
                    ? {
                          listUnitPriceKopecks:
                              Number(item.pricing.list_unit_price_kopecks) || 0,
                          finalUnitPriceKopecks:
                              Number(item.pricing.final_unit_price_kopecks) || 0,
                          lineTotalKopecks: Number(item.pricing.line_total_kopecks) || 0,
                      }
                    : {
                          listUnitPriceKopecks:
                              Math.round((Number(snapshot?.price) || 0) * 100),
                          finalUnitPriceKopecks:
                              Math.round((Number(snapshot?.price) || 0) * 100),
                          lineTotalKopecks:
                              Math.round((Number(snapshot?.price) || 0) * 100) * qty,
                      };

            let lineKind = "user";
            if (isSystem && lineKey.startsWith("gift:")) {
                lineKind = "gift";
            } else if (isSystem && lineKey.startsWith("complement:")) {
                lineKind = "complement";
            } else if (isSystem) {
                lineKind = "system";
            }

            return {
                lineKey,
                origin,
                isSystem,
                lineKind,
                productId,
                qty,
                productSnapshot: snapshot,
                pricing,
            };
        })
        .filter(Boolean);
}

function normalizeDeliveryPricing(raw) {
    if (!raw || typeof raw !== "object") {
        return null;
    }

    const itemsTotalKopecks = Number(raw.items_total_kopecks) || 0;
    const deliveryFeeKopecks = Number(raw.delivery_fee_kopecks) || 0;
    const grandTotalKopecks =
        Number(raw.grand_total_kopecks) || itemsTotalKopecks + deliveryFeeKopecks;

    return {
        method: raw.method != null ? String(raw.method) : null,
        itemsPayableKopecks: Number(raw.items_payable_kopecks) || 0,
        deliveryFeeKopecks,
        isFree: Boolean(raw.is_free),
        remainingToFreeKopecks: Number(raw.remaining_to_free_kopecks) || 0,
        itemsTotalKopecks,
        grandTotalKopecks,
        itemsTotalRub:
            raw.items_total_rub != null
                ? roundRubles2(Number(raw.items_total_rub))
                : roundRubles2(itemsTotalKopecks / 100),
        deliveryFeeRub:
            raw.delivery_fee_rub != null
                ? roundRubles2(Number(raw.delivery_fee_rub))
                : roundRubles2(deliveryFeeKopecks / 100),
        grandTotalRub:
            raw.grand_total_rub != null
                ? roundRubles2(Number(raw.grand_total_rub))
                : roundRubles2(grandTotalKopecks / 100),
    };
}

export const useCartStore = defineStore("cart", {
    state: () => ({
        cartItems: [],
        subtotalKopecks: 0,
        subtotalUserKopecks: 0,
        subtotalSystemKopecks: 0,
        promoState: {},
        deliveryPricing: null,
        loading: false,
        error: null,
    }),
    getters: {
        userItems(state) {
            return state.cartItems.filter((item) => !item.isSystem);
        },
        systemItems(state) {
            return state.cartItems.filter((item) => item.isSystem);
        },
        cartQuantityByProduct: (state) => (id) => {
            const item = state.cartItems.find(
                (i) => i.productId === id && !i.isSystem,
            );
            return item ? item.qty : 0;
        },
        cartTotalItems(state) {
            return state.cartItems.reduce(
                (sum, item) => sum + (item.isSystem ? 0 : item.qty),
                0,
            );
        },
        cartSystemItemsCount(state) {
            return state.cartItems.reduce(
                (sum, item) => sum + (item.isSystem ? item.qty : 0),
                0,
            );
        },
        cartTotalAmount(state) {
            if (state.subtotalKopecks > 0) {
                return roundRubles2(state.subtotalKopecks / 100);
            }
            const kopecks = state.cartItems.reduce(
                (sum, item) => sum + (Number(item.pricing?.lineTotalKopecks) || 0),
                0,
            );
            return roundRubles2(kopecks / 100);
        },
        cartUserTotalAmount(state) {
            return roundRubles2((Number(state.subtotalUserKopecks) || 0) / 100);
        },
        cartSystemTotalAmount(state) {
            return roundRubles2((Number(state.subtotalSystemKopecks) || 0) / 100);
        },
        hasDeliveryPricing(state) {
            return state.deliveryPricing != null;
        },
        itemsTotalAmount(state) {
            if (state.deliveryPricing?.itemsTotalRub != null) {
                return state.deliveryPricing.itemsTotalRub;
            }
            if (state.subtotalKopecks > 0) {
                return roundRubles2(state.subtotalKopecks / 100);
            }
            return 0;
        },
        deliveryFeeAmount(state) {
            return state.deliveryPricing?.deliveryFeeRub ?? 0;
        },
        grandTotalWithDelivery(state) {
            if (state.deliveryPricing?.grandTotalRub != null) {
                return state.deliveryPricing.grandTotalRub;
            }
            return this.cartTotalAmount;
        },
        isDeliveryFree(state) {
            return state.deliveryPricing?.isFree ?? true;
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
            this.subtotalKopecks = Number(cart?.subtotal_kopecks) || 0;
            this.subtotalUserKopecks = Number(cart?.subtotal_user_kopecks) || 0;
            this.subtotalSystemKopecks = Number(cart?.subtotal_system_kopecks) || 0;
            this.promoState =
                cart?.promo_state && typeof cart.promo_state === "object"
                    ? cart.promo_state
                    : {};
            emitDomainEvent(DOMAIN_EVENTS.CART_CHANGED, { items: this.cartItems });
        },

        applyDeliveryPricingSnapshot(deliveryPricing) {
            this.deliveryPricing = normalizeDeliveryPricing(deliveryPricing);
        },

        _applyStateData(data) {
            applyCartAndDeliveryFromState(this, data);
            applyShoppingMetaFromState(data);
        },

        async addToCart(product, qty = 1) {
            if (!product || !product.id) return;
            const id = product.id;
            const add = Math.max(1, Number(qty) || 1);
            const existing = this.cartItems.find(
                (i) => i.productId === id && !i.isSystem,
            );
            const nextQty = (existing ? existing.qty : 0) + add;
            await this._upsertLine(id, nextQty);
        },

        async incrementCart(productId) {
            const item = this.cartItems.find(
                (i) => i.productId === productId && !i.isSystem,
            );
            if (!item) return;
            await this._upsertLine(productId, item.qty + 1);
        },

        async decrementCart(productId) {
            const item = this.cartItems.find(
                (i) => i.productId === productId && !i.isSystem,
            );
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
