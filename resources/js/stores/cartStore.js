import { defineStore } from "pinia";
import { DOMAIN_EVENTS, emitDomainEvent } from "../shared/domainEvents";
import { roundRubles2 } from "../utils/moneyFormat";
import { normalizeBenefitsProgress } from "../features/checkout/normalizeBenefitsProgress";
import { useCheckoutStore } from "./checkoutStore";

export const useCartStore = defineStore("cart", {
    state: () => ({
        promoState: {},
        deliveryPricing: null,
        benefitsProgress: null,
        loading: false,
        error: null,
    }),
    getters: {
        cartItems() {
            return useCheckoutStore().cartItems;
        },
        userItems(state) {
            return useCheckoutStore().userItems;
        },
        systemItems() {
            return useCheckoutStore().cartItems.filter((item) => item.isSystem);
        },
        cartQuantityByProduct: () => (id) => {
            const item = useCheckoutStore().cartItems.find(
                (i) => i.productId === id && !i.isSystem,
            );
            return item ? item.qty : 0;
        },
        cartTotalItems() {
            return useCheckoutStore().cartTotalItems;
        },
        cartSystemItemsCount() {
            return useCheckoutStore().cartItems.reduce(
                (sum, item) => sum + (item.isSystem ? item.qty : 0),
                0,
            );
        },
        cartTotalAmount() {
            return useCheckoutStore().itemsTotalRubles;
        },
        cartUserTotalAmount() {
            return useCheckoutStore().itemsTotalRubles;
        },
        cartSystemTotalAmount() {
            return 0;
        },
        hasDeliveryPricing(state) {
            return state.deliveryPricing != null;
        },
        itemsTotalAmount() {
            return useCheckoutStore().itemsTotalRubles;
        },
        deliveryFeeAmount(state) {
            return state.deliveryPricing?.deliveryFeeRub ?? 0;
        },
        grandTotalWithDelivery(state) {
            if (state.deliveryPricing?.grandTotalRub != null) {
                return state.deliveryPricing.grandTotalRub;
            }
            return useCheckoutStore().itemsTotalRubles;
        },
        isDeliveryFree(state) {
            if (state.deliveryPricing == null) {
                return false;
            }
            return Boolean(state.deliveryPricing.isFree);
        },
        hasBenefitsProgress(state) {
            return state.benefitsProgress != null;
        },
    },
    actions: {
        initFromStorage() {},

        applyServerSnapshot(cart) {
            if (!cart || typeof cart !== "object") {
                return;
            }

            this.promoState =
                cart?.promo_state && typeof cart.promo_state === "object"
                    ? cart.promo_state
                    : {};
        },

        applyDeliveryPricingSnapshot(deliveryPricing) {
            if (!deliveryPricing || typeof deliveryPricing !== "object") {
                this.deliveryPricing = null;
                return;
            }

            const itemsTotalKopecks = Number(deliveryPricing.items_total_kopecks) || 0;
            const deliveryFeeKopecks = Number(deliveryPricing.delivery_fee_kopecks) || 0;
            const grandTotalKopecks =
                Number(deliveryPricing.grand_total_kopecks) ||
                itemsTotalKopecks + deliveryFeeKopecks;

            this.deliveryPricing = {
                method:
                    deliveryPricing.method != null
                        ? String(deliveryPricing.method)
                        : null,
                itemsPayableKopecks: Number(deliveryPricing.items_payable_kopecks) || 0,
                deliveryFeeKopecks,
                isFree: Boolean(deliveryPricing.is_free),
                isPreview: Boolean(deliveryPricing.is_preview),
                remainingToFreeKopecks: Number(deliveryPricing.remaining_to_free_kopecks) || 0,
                itemsTotalKopecks,
                grandTotalKopecks,
                itemsTotalRub:
                    deliveryPricing.items_total_rub != null
                        ? roundRubles2(Number(deliveryPricing.items_total_rub))
                        : roundRubles2(itemsTotalKopecks / 100),
                deliveryFeeRub:
                    deliveryPricing.delivery_fee_rub != null
                        ? roundRubles2(Number(deliveryPricing.delivery_fee_rub))
                        : roundRubles2(deliveryFeeKopecks / 100),
                grandTotalRub:
                    deliveryPricing.grand_total_rub != null
                        ? roundRubles2(Number(deliveryPricing.grand_total_rub))
                        : roundRubles2(grandTotalKopecks / 100),
            };
        },

        applyBenefitsProgressSnapshot(benefitsProgress) {
            this.benefitsProgress = normalizeBenefitsProgress(benefitsProgress);
        },

        async addToCart(product, qty = 1) {
            if (!product || !product.id) return;
            const id = product.id;
            const add = Math.max(1, Number(qty) || 1);
            const checkoutStore = useCheckoutStore();
            const existing = checkoutStore.cartItems.find(
                (i) => i.productId === id && !i.isSystem,
            );
            const nextQty = (existing ? existing.qty : 0) + add;
            await this._upsertLine(id, nextQty);
        },

        async incrementCart(productId) {
            const checkoutStore = useCheckoutStore();
            const item = checkoutStore.cartItems.find(
                (i) => i.productId === productId && !i.isSystem,
            );
            if (!item) return;
            await this._upsertLine(productId, item.qty + 1);
        },

        async decrementCart(productId) {
            const checkoutStore = useCheckoutStore();
            const item = checkoutStore.cartItems.find(
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
                await useCheckoutStore().updateCartLine(productId, 0);
            } catch (e) {
                this.error =
                    e?.response?.data?.message || "Не удалось обновить корзину.";
                throw e;
            } finally {
                this.loading = false;
            }
        },

        async clear() {
            this.loading = true;
            this.error = null;
            try {
                const checkoutStore = useCheckoutStore();
                for (const item of [...checkoutStore.userItems]) {
                    await checkoutStore.updateCartLine(item.productId, 0);
                }
                emitDomainEvent(DOMAIN_EVENTS.CART_CLEARED);
            } catch (e) {
                this.error =
                    e?.response?.data?.message || "Не удалось очистить корзину.";
                throw e;
            } finally {
                this.loading = false;
            }
        },

        async recalculateFromServer() {
            /* пересчёт доставки/акций — отдельный контур, пока no-op */
        },

        async _upsertLine(productId, quantity) {
            this.loading = true;
            this.error = null;
            try {
                await useCheckoutStore().updateCartLine(productId, quantity);
            } catch (e) {
                this.error =
                    e?.response?.data?.message || "Не удалось обновить корзину.";
                throw e;
            } finally {
                this.loading = false;
            }
        },
    },
});
