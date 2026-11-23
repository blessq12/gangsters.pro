import { defineStore } from "pinia";
import {
    bootstrapCheckoutStoreSession,
    persistCheckoutSession,
    placeOrderOnServer,
    recomputeCheckout,
    setCheckoutPromotionGift,
    upsertLocalCartLine,
} from "./application/session";
import {
    buildCatalogCartLinePayload,
    CHECKOUT_PERSONS_DEFAULT,
    normalizePaymentPatch,
} from "./domain/checkoutServerMappers";
import {
    buildCheckoutSessionSnapshot,
    CHECKOUT_WIZARD_STEPS,
    clearCheckoutSessionPayload,
} from "./application/session";
import { mapServerWizardStep } from "./application/wizard";
import { normalizeBenefitsProgress } from "./domain/normalizeCheckoutCart";
import { normalizeOrderPreview } from "./domain/normalizeOrderPreview";
import {
    normalizeCheckoutCartBlock,
    selectedGiftCartLine,
} from "./domain/normalizeCheckoutCart";
import { DOMAIN_EVENTS, emitDomainEvent } from "../../platform/domainEvents";
import { roundRubles2 } from "../../platform/moneyFormat";
import { useCatalogStore } from "../catalog/store";

function resolveComplementProductIds() {
    const catalogStore = useCatalogStore();
    const ids = new Set();
    for (const product of catalogStore.complementProducts || []) {
        const id = Number(product?.id);
        if (Number.isFinite(id) && id > 0) {
            ids.add(id);
        }
    }
    return ids;
}

export const useCheckoutStore = defineStore("checkout", {
    state: () => ({
        clientRequestId: null,
        /** GET /checkout/context: меню, правила акций, зоны, rules_version. */
        context: null,
        cartItems: [],
        itemsTotalRubles: 0,
        itemsSubtotalRubles: 0,
        /** Снимки локально сформированного заказа (для экрана подтверждения). */
        formedClient: null,
        formedDelivery: null,
        formedPayment: null,
        deliveryInfo: {
            method: null,
            address: null,
            comment: "",
            persons: CHECKOUT_PERSONS_DEFAULT,
            scheduledAt: null,
        },
        paymentInfo: {
            method: null,
            changeFrom: null,
        },
        guestContact: {
            name: "",
            phone: "",
            email: "",
        },
        promotions: {
            freeRollGiftProductId: null,
        },
        /** Выбор бесплатных комплектов: productId → qty (0…entitled). */
        complementSelections: {},
        /** Снимок entitled для авто-донабора при росте роллов. */
        complementEntitledSnapshot: 0,
        promoState: {},
        deliveryPricing: null,
        benefitsProgress: null,
        suggestedStep: null,
        wizardCanConfirm: false,
        wizardMissingBlocks: [],
        orderPreview: null,
        loading: false,
        flushing: false,
        cartLoading: false,
        cartError: null,
        error: null,
        sessionReady: false,
    }),
    getters: {
        isDraft() {
            return true;
        },
        hasCheckout() {
            return this.sessionReady;
        },
        userItems(state) {
            return state.cartItems.filter((item) => !item.isSystem);
        },
        systemItems(state) {
            return state.cartItems.filter((item) => item.isSystem);
        },
        hasCartItems(state) {
            return state.cartItems.some((item) => !item.isSystem);
        },
        /** User-линии меню без комплектных (палочки и т.п.). */
        hasMenuUserItems(state) {
            const complementIds = resolveComplementProductIds();
            return state.cartItems.some(
                (item) =>
                    !item.isSystem
                    && !complementIds.has(Number(item.productId)),
            );
        },
        cartTotalItems(state) {
            return state.cartItems.reduce(
                (sum, item) => sum + (item.isSystem ? 0 : item.qty),
                0,
            );
        },
        cartQuantityByProduct: (state) => (id) => {
            const item = state.cartItems.find(
                (entry) => entry.productId === id && !entry.isSystem,
            );
            return item ? item.qty : 0;
        },
        complementSelectionQty: (state) => (productId) => {
            const id = Number(productId);
            if (!Number.isFinite(id) || id < 1) {
                return 0;
            }
            return Number(state.complementSelections[id]) || 0;
        },
        cartSystemItemsCount(state) {
            return state.cartItems.reduce(
                (sum, item) => sum + (item.isSystem ? item.qty : 0),
                0,
            );
        },
        cartTotalAmount(state) {
            return state.itemsTotalRubles;
        },
        cartUserTotalAmount(state) {
            return state.itemsTotalRubles;
        },
        cartSystemTotalAmount() {
            return 0;
        },
        itemsTotalAmount(state) {
            return state.itemsTotalRubles;
        },
        hasDeliveryPricing(state) {
            return state.deliveryPricing != null;
        },
        deliveryFeeAmount(state) {
            return state.deliveryPricing?.deliveryFeeRub ?? 0;
        },
        grandTotalWithDelivery(state) {
            if (state.deliveryPricing?.grandTotalRub != null) {
                return state.deliveryPricing.grandTotalRub;
            }
            return state.itemsTotalRubles;
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
        setContext(context) {
            this.context = context && typeof context === "object" ? context : null;
        },

        /**
         * Применяет локально сформированный заказ. Формы клиента (доставка, оплата, контакт) —
         * это намерение, поэтому здесь не перезаписываются.
         */
        applyFormation(data) {
            if (!data || typeof data !== "object") {
                return;
            }

            const cart = normalizeCheckoutCartBlock(data.cart);
            this.cartItems = cart.items;
            this.itemsTotalRubles = cart.itemsTotalRubles;
            this.itemsSubtotalRubles = cart.itemsSubtotalRubles;

            if (Object.prototype.hasOwnProperty.call(data, "client")) {
                this.formedClient = data.client ?? null;
            }
            this.formedDelivery = data.delivery ?? null;
            this.formedPayment = data.payment ?? null;

            this._applyCartPromoSnapshot(data.cart ?? null);
            this._syncSelectedGift();
            if (Object.prototype.hasOwnProperty.call(data, "delivery_pricing")) {
                this._applyDeliveryPricingSnapshot(data.delivery_pricing);
            }
            if (Object.prototype.hasOwnProperty.call(data, "benefits_progress")) {
                this._applyBenefitsProgressSnapshot(data.benefits_progress);
            }
            if (Object.prototype.hasOwnProperty.call(data, "wizard")) {
                this._applyWizardSnapshot(data.wizard);
            }
            if (Object.prototype.hasOwnProperty.call(data, "order_preview")) {
                this._applyOrderPreviewSnapshot(data.order_preview);
            }

            this._syncComplementSelections();

            this.persistSession();

            emitDomainEvent(DOMAIN_EVENTS.CART_CHANGED, { items: this.cartItems });
        },

        _applyCartPromoSnapshot(cart) {
            if (!cart || typeof cart !== "object") {
                return;
            }

            this.promoState =
                cart?.promo_state && typeof cart.promo_state === "object"
                    ? cart.promo_state
                    : {};
        },

        _syncSelectedGift() {
            const giftLine = selectedGiftCartLine(this.cartItems);
            if (giftLine) {
                this.promotions = {
                    ...this.promotions,
                    freeRollGiftProductId: giftLine.productId,
                };
                return;
            }

            const selectedFromPromo =
                Number(this.promoState?.gift_promotion?.selected_product_id) || 0;
            if (selectedFromPromo > 0) {
                this.promotions = {
                    ...this.promotions,
                    freeRollGiftProductId: selectedFromPromo,
                };
                return;
            }

            if (this.promoState?.gift_promotion?.eligible !== true) {
                this.promotions = { ...this.promotions, freeRollGiftProductId: null };
            }
        },

        _applyDeliveryPricingSnapshot(deliveryPricing) {
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
                inZone:
                    deliveryPricing.in_zone === true
                        ? true
                        : deliveryPricing.in_zone === false
                          ? false
                          : null,
                isRemote: Boolean(deliveryPricing.is_remote),
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

        _applyBenefitsProgressSnapshot(benefitsProgress) {
            this.benefitsProgress = normalizeBenefitsProgress(benefitsProgress);
        },

        _applyWizardSnapshot(wizard) {
            if (!wizard || typeof wizard !== "object") {
                this.suggestedStep = null;
                this.wizardCanConfirm = false;
                this.wizardMissingBlocks = [];
                return;
            }

            const step = mapServerWizardStep(wizard.suggested_step);
            this.suggestedStep =
                step && CHECKOUT_WIZARD_STEPS.includes(step) ? step : null;
            this.wizardCanConfirm = Boolean(wizard.can_confirm);
            this.wizardMissingBlocks = Array.isArray(wizard.missing_blocks)
                ? wizard.missing_blocks.map(String)
                : [];
        },

        _applyOrderPreviewSnapshot(orderPreview) {
            this.orderPreview = normalizeOrderPreview(orderPreview);
        },

        _syncComplementSelections() {
            const entitled =
                Number(this.benefitsProgress?.complement?.entitledSetCount)
                || Number(this.orderPreview?.benefits?.complement?.entitledSetCount)
                || 0;
            const prevEntitled = Number(this.complementEntitledSnapshot) || 0;
            const catalogStore = useCatalogStore();
            const productIds = (catalogStore.complementProducts || [])
                .map((product) => Number(product?.id))
                .filter((id) => Number.isFinite(id) && id > 0);

            if (entitled <= 0) {
                this.complementSelections = {};
                this.complementEntitledSnapshot = 0;
                return;
            }

            if (productIds.length === 0) {
                return;
            }

            const next = { ...this.complementSelections };

            for (const id of productIds) {
                let selected = next[id];

                if (selected === undefined) {
                    selected = entitled;
                } else if (selected > entitled) {
                    selected = entitled;
                } else if (entitled > prevEntitled && selected === prevEntitled) {
                    selected = entitled;
                }

                next[id] = selected;
            }

            for (const rawId of Object.keys(next)) {
                const id = Number(rawId);
                if (!productIds.includes(id)) {
                    delete next[id];
                }
            }

            this.complementSelections = next;
            this.complementEntitledSnapshot = entitled;
        },

        setSuggestedStep(step) {
            const mapped = mapServerWizardStep(step);
            if (mapped && CHECKOUT_WIZARD_STEPS.includes(mapped)) {
                this.suggestedStep = mapped;
            } else if (step === null) {
                this.suggestedStep = null;
            }
        },

        persistSession() {
            persistCheckoutSession(buildCheckoutSessionSnapshot(this));
        },

        restoreLocalCart(localCart) {
            if (!Array.isArray(localCart)) {
                return;
            }
            this.cartItems = localCart;
            this.itemsSubtotalRubles = localCart.reduce(
                (sum, item) => sum + (Number(item.pricing?.lineTotalKopecks) || 0) / 100,
                0,
            );
            this.itemsTotalRubles = this.itemsSubtotalRubles;
        },

        bootstrapSession() {
            return bootstrapCheckoutStoreSession(this);
        },

        clearAfterCompleted() {
            this.clientRequestId = null;
            this.cartItems = [];
            this.itemsTotalRubles = 0;
            this.itemsSubtotalRubles = 0;
            this.formedClient = null;
            this.formedDelivery = null;
            this.formedPayment = null;
            this.promoState = {};
            this.deliveryPricing = null;
            this.benefitsProgress = null;
            this.cartLoading = false;
            this.cartError = null;
            this.loading = false;
            this.flushing = false;
            this.clearLocalForms();
            this.suggestedStep = null;
            this.wizardCanConfirm = false;
            this.wizardMissingBlocks = [];
            this.orderPreview = null;
            this.sessionReady = false;
            this.error = null;
            clearCheckoutSessionPayload();
        },

        clearLocalForms() {
            this.deliveryInfo = {
                method: null,
                address: null,
                comment: "",
                persons: CHECKOUT_PERSONS_DEFAULT,
                scheduledAt: null,
            };
            this.paymentInfo = {
                method: null,
                changeFrom: null,
            };
            this.guestContact = { name: "", phone: "", email: "" };
            this.promotions = { freeRollGiftProductId: null };
            this.complementSelections = {};
            this.complementEntitledSnapshot = 0;
        },

        async setComplementSelection(productId, quantity) {
            const id = Number(productId);
            if (!Number.isFinite(id) || id < 1) {
                return;
            }

            const entitled =
                Number(this.benefitsProgress?.complement?.entitledSetCount)
                || Number(this.orderPreview?.benefits?.complement?.entitledSetCount)
                || 0;
            const nextQty = Math.max(0, Math.min(entitled, Number(quantity) || 0));

            this.complementSelections = {
                ...this.complementSelections,
                [id]: nextQty,
            };
            this.persistSession();
            await recomputeCheckout(this);
        },

        patchLocal(partial) {
            if (!partial || typeof partial !== "object") {
                return;
            }
            if (partial.deliveryInfo) {
                this.deliveryInfo = { ...this.deliveryInfo, ...partial.deliveryInfo };
            }
            if (partial.paymentInfo) {
                this.paymentInfo = { ...this.paymentInfo, ...partial.paymentInfo };
            }
            if (partial.guestContact) {
                this.guestContact = { ...this.guestContact, ...partial.guestContact };
            }
            if (partial.promotions) {
                this.promotions = { ...this.promotions, ...partial.promotions };
            }
        },

        setDeliveryInfo(payload) {
            this.patchLocal({ deliveryInfo: payload || {} });
        },

        setPaymentInfo(payload) {
            this.patchLocal({ paymentInfo: normalizePaymentPatch(payload) });
        },

        setGuestContact(payload) {
            this.patchLocal({ guestContact: payload || {} });
        },

        patchDeliveryAddress(partial) {
            const previous =
                this.deliveryInfo.address &&
                typeof this.deliveryInfo.address === "object"
                    ? this.deliveryInfo.address
                    : {};
            const next = {
                ...previous,
                ...(partial || {}),
            };

            const streetChanged =
                partial?.street != null && partial.street !== previous.street;
            const houseChanged =
                partial?.house != null && partial.house !== previous.house;

            if (streetChanged || houseChanged) {
                delete next.latitude;
                delete next.longitude;
                delete next.lat;
                delete next.lng;
            }

            this.deliveryInfo = {
                ...this.deliveryInfo,
                address: next,
            };
        },

        updateCartLine(product, quantity, payload = null) {
            upsertLocalCartLine(this, product, quantity, payload);
            if (!this.hasMenuUserItems) {
                return this.clearCart();
            }
            return recomputeCheckout(this);
        },

        async addToCart(product, qty = 1) {
            if (!product || !product.id) {
                return;
            }
            const id = product.id;
            const add = Math.max(1, Number(qty) || 1);
            const existing = this.cartItems.find(
                (item) => item.productId === id && !item.isSystem,
            );
            const nextQty = (existing ? existing.qty : 0) + add;
            const payload = buildCatalogCartLinePayload(product, existing?.payload);
            await this._upsertCartLine(product, nextQty, payload);
        },

        async incrementCart(productId) {
            const item = this.cartItems.find(
                (entry) => entry.productId === productId && !entry.isSystem,
            );
            if (!item) {
                return;
            }
            const product = {
                id: productId,
                name: item.productSnapshot?.name,
                price: { amount: item.productSnapshot?.price },
            };
            await this._upsertCartLine(product, item.qty + 1, item.payload ?? null);
        },

        async decrementCart(productId) {
            const item = this.cartItems.find(
                (entry) => entry.productId === productId && !entry.isSystem,
            );
            if (!item) {
                return;
            }
            const product = {
                id: productId,
                name: item.productSnapshot?.name,
                price: { amount: item.productSnapshot?.price },
            };
            const next = item.qty - 1;
            if (next <= 0) {
                await this.removeFromCart(productId);
            } else {
                await this._upsertCartLine(product, next, item.payload ?? null);
            }
        },

        async removeFromCart(productId) {
            this.cartLoading = true;
            this.cartError = null;
            try {
                const item = this.cartItems.find(
                    (entry) => entry.productId === productId && !entry.isSystem,
                );
                const product = {
                    id: productId,
                    name: item?.productSnapshot?.name,
                    price: { amount: item?.productSnapshot?.price },
                };
                upsertLocalCartLine(this, product, 0, item?.payload ?? null);
                if (!this.hasMenuUserItems) {
                    await this.clearCart();
                    return;
                }
                await recomputeCheckout(this);
            } catch (e) {
                this.cartError =
                    e?.response?.data?.message || "Не удалось обновить корзину.";
                throw e;
            } finally {
                this.cartLoading = false;
            }
        },

        async clearCart() {
            this.cartLoading = true;
            this.cartError = null;
            try {
                this.resetQuoteState();
                this.persistSession();
                emitDomainEvent(DOMAIN_EVENTS.CART_CLEARED);
            } catch (e) {
                this.cartError =
                    e?.response?.data?.message || "Не удалось очистить корзину.";
                throw e;
            } finally {
                this.cartLoading = false;
            }
        },

        /**
         * Сброс корзины и сформированного заказа (комплекты, promo, pricing).
         * Формы доставки/оплаты/гостя не трогает.
         */
        resetQuoteState() {
            this.cartItems = [];
            this.itemsTotalRubles = 0;
            this.itemsSubtotalRubles = 0;
            this.promoState = {};
            this.deliveryPricing = null;
            this.benefitsProgress = null;
            this.orderPreview = null;
            this.complementSelections = {};
            this.complementEntitledSnapshot = 0;
            this.promotions = { freeRollGiftProductId: null };
            this.suggestedStep = null;
            this.wizardCanConfirm = false;
            this.wizardMissingBlocks = [];
            this.formedClient = null;
            this.formedDelivery = null;
            this.formedPayment = null;
            this.cartError = null;
            this.error = null;
        },

        async _upsertCartLine(product, quantity, payload = null) {
            this.cartLoading = true;
            this.cartError = null;
            try {
                upsertLocalCartLine(this, product, quantity, payload);
                if (!this.hasMenuUserItems) {
                    await this.clearCart();
                    return;
                }
                await recomputeCheckout(this);
            } catch (e) {
                this.cartError =
                    e?.response?.data?.message || "Не удалось обновить корзину.";
                throw e;
            } finally {
                this.cartLoading = false;
            }
        },

        recompute(selectedAddress = null, options = {}) {
            return recomputeCheckout(this, selectedAddress, options);
        },

        confirmCheckout() {
            return placeOrderOnServer(this);
        },

        setPromotionGift(productId) {
            return setCheckoutPromotionGift(this, productId);
        },
    },
});
