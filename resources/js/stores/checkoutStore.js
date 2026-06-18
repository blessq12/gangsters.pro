import { defineStore } from "pinia";
import {
    bootstrapCheckoutSession,
    flushClientToServer,
    flushDeliveryToServer,
    flushPaymentToServer,
    persistCheckoutSession,
    placeOrderOnServer,
    refreshOrderDraftPreview,
    setCheckoutPromotionGift,
    upsertLocalCartLine,
} from "../features/checkout/checkoutSessionService";
import {
    buildCatalogCartLinePayload,
    mapClientToGuestContact,
    mapDeliveryToLocal,
    mapPaymentToLocal,
    normalizePaymentPatch,
} from "../features/checkout/checkoutServerMappers";
import {
    buildCheckoutSessionSnapshot,
    CHECKOUT_WIZARD_STEPS,
    clearCheckoutSessionPayload,
} from "../features/checkout/checkoutSessionStorage";
import { normalizeBenefitsProgress } from "../features/checkout/normalizeBenefitsProgress";
import { normalizeOrderPreview } from "../features/checkout/normalizeOrderPreview";
import {
    normalizeCheckoutCartBlock,
    selectedGiftCartLine,
} from "../features/checkout/normalizeCheckoutCart";
import { DOMAIN_EVENTS, emitDomainEvent } from "../shared/domainEvents";
import { roundRubles2 } from "../utils/moneyFormat";

export const useCheckoutStore = defineStore("checkout", {
    state: () => ({
        clientRequestId: null,
        cartItems: [],
        itemsTotalRubles: 0,
        itemsSubtotalRubles: 0,
        serverClient: null,
        serverDelivery: null,
        serverPayment: null,
        deliveryInfo: {
            method: null,
            address: null,
            comment: "",
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
        customerComment: "",
        promotions: {
            freeRollGiftProductId: null,
        },
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
        /** Инкремент при сбросе сессии — отсекает устаревшие preview-ответы. */
        previewRequestSeq: 0,
        /** Гостевой адрес в черновике — не перезаписывать текст полей из preview. */
        deliveryAddressDraftDirty: false,
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
        applyFromServer(data) {
            if (!data || typeof data !== "object") {
                return;
            }

            this.clientRequestId = data.client_request_id ?? this.clientRequestId;

            const cart = normalizeCheckoutCartBlock(data.cart);
            this.cartItems = cart.items;
            this.itemsTotalRubles = cart.itemsTotalRubles;
            this.itemsSubtotalRubles = cart.itemsSubtotalRubles;

            if (Object.prototype.hasOwnProperty.call(data, "client")) {
                this.serverClient = data.client ?? null;
                if (data.client?.kind === "guest") {
                    this.guestContact = mapClientToGuestContact(data.client);
                }
            }

            if (Object.prototype.hasOwnProperty.call(data, "delivery")) {
                this.serverDelivery = data.delivery ?? null;
                if (data.delivery && typeof data.delivery === "object") {
                    const mapped = mapDeliveryToLocal(data.delivery);
                    if (this.deliveryAddressDraftDirty) {
                        this.deliveryInfo = {
                            ...this.deliveryInfo,
                            method: mapped.method ?? this.deliveryInfo.method,
                            comment: mapped.comment ?? this.deliveryInfo.comment,
                            scheduledAt:
                                mapped.scheduledAt ?? this.deliveryInfo.scheduledAt,
                        };
                    } else {
                        this.deliveryInfo = mapped;
                    }
                }
            }

            if (Object.prototype.hasOwnProperty.call(data, "payment")) {
                this.serverPayment = data.payment ?? null;
                if (data.payment && typeof data.payment === "object") {
                    this.paymentInfo = mapPaymentToLocal(data.payment);
                }
            }

            this._applyCartPromoSnapshot(data.cart ?? null);
            this._syncSelectedGiftFromServer();
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

        _syncSelectedGiftFromServer() {
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

            const step = wizard.suggested_step;
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

        setSuggestedStep(step) {
            if (step && CHECKOUT_WIZARD_STEPS.includes(step)) {
                this.suggestedStep = step;
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
            return bootstrapCheckoutSession(this);
        },

        clearAfterCompleted() {
            this.previewRequestSeq += 1;
            this.clientRequestId = null;
            this.cartItems = [];
            this.itemsTotalRubles = 0;
            this.itemsSubtotalRubles = 0;
            this.serverClient = null;
            this.serverDelivery = null;
            this.serverPayment = null;
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
            this.deliveryAddressDraftDirty = false;
            this.deliveryInfo = {
                method: null,
                address: null,
                comment: "",
                scheduledAt: null,
            };
            this.paymentInfo = {
                method: null,
                changeFrom: null,
            };
            this.guestContact = { name: "", phone: "", email: "" };
            this.customerComment = "";
            this.promotions = { freeRollGiftProductId: null };
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
            if (typeof partial.customerComment === "string") {
                this.customerComment = partial.customerComment;
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

        setCustomerComment(comment) {
            this.patchLocal({ customerComment: comment || "" });
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
            }

            this.deliveryInfo = {
                ...this.deliveryInfo,
                address: next,
            };
        },

        setDeliveryAddressDraftDirty(dirty = true) {
            this.deliveryAddressDraftDirty = Boolean(dirty);
        },

        invalidateDeliveryZoneResolve() {
            if (this.deliveryPricing) {
                this.deliveryPricing = {
                    ...this.deliveryPricing,
                    inZone: null,
                };
            }

            if (this.orderPreview?.totals) {
                this.orderPreview = {
                    ...this.orderPreview,
                    totals: {
                        ...this.orderPreview.totals,
                        inZone: null,
                    },
                };
            }
        },

        updateCartLine(product, quantity, payload = null) {
            upsertLocalCartLine(this, product, quantity, payload);
            return refreshOrderDraftPreview(this);
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
                await refreshOrderDraftPreview(this);
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
                this.cartItems = this.cartItems.filter((item) => item.isSystem);
                this.itemsTotalRubles = 0;
                this.itemsSubtotalRubles = 0;
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

        async _upsertCartLine(product, quantity, payload = null) {
            this.cartLoading = true;
            this.cartError = null;
            try {
                upsertLocalCartLine(this, product, quantity, payload);
                await refreshOrderDraftPreview(this);
            } catch (e) {
                this.cartError =
                    e?.response?.data?.message || "Не удалось обновить корзину.";
                throw e;
            } finally {
                this.cartLoading = false;
            }
        },

        flushClientToServer(options = {}) {
            return flushClientToServer(this, options);
        },

        flushDeliveryToServer(selectedAddress = null) {
            return flushDeliveryToServer(this, selectedAddress);
        },

        flushPaymentToServer() {
            return flushPaymentToServer(this);
        },

        confirmCheckout() {
            return placeOrderOnServer(this);
        },

        setPromotionGift(productId) {
            return setCheckoutPromotionGift(this, productId);
        },
    },
});

/** @deprecated Используйте useCheckoutStore */
export const useCheckoutPricingStore = useCheckoutStore;
