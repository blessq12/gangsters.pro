import { defineStore } from "pinia";
import {
    setCheckoutPromotionGift,
    updateCheckoutCartLine,
} from "../features/checkout/checkoutCartCommands";
import {
    bootstrapCheckoutSession,
    confirmCheckoutOnServer,
    ensureDraftCheckout,
    flushCheckoutToServer,
    flushClientToServer,
    flushDeliveryToServer,
    flushPaymentToServer,
    persistCheckoutSession,
    tryRestoreCheckoutSession,
} from "../features/checkout/checkoutFlushCommands";
import {
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
import { normalizeCheckoutCartBlock } from "../features/checkout/normalizeCheckoutCart";
import { DOMAIN_EVENTS, emitDomainEvent } from "../shared/domainEvents";
import { useCheckoutPricingStore } from "./checkoutPricingStore";

export const useCheckoutStore = defineStore("checkout", {
    state: () => ({
        checkoutId: null,
        status: null,
        cartItems: [],
        itemsTotalRubles: 0,
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
        suggestedStep: null,
        loading: false,
        flushing: false,
        error: null,
        sessionReady: false,
    }),
    getters: {
        isDraft(state) {
            return state.status === "draft";
        },
        hasCheckout(state) {
            return Boolean(state.checkoutId) && state.status === "draft";
        },
        userItems(state) {
            return state.cartItems.filter((item) => !item.isSystem);
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
    },
    actions: {
        applyFromServer(data) {
            if (!data || typeof data !== "object") {
                return;
            }

            this.checkoutId = data.checkout_id ?? this.checkoutId;
            this.status = data.status ?? this.status;

            const cart = normalizeCheckoutCartBlock(data.cart);
            this.cartItems = cart.items;
            this.itemsTotalRubles = cart.itemsTotalRubles;

            if (data.client && typeof data.client === "object") {
                this.serverClient = data.client;
                if (data.client.kind === "guest") {
                    this.guestContact = mapClientToGuestContact(data.client);
                }
            }

            if (data.delivery && typeof data.delivery === "object") {
                this.serverDelivery = data.delivery;
                this.deliveryInfo = mapDeliveryToLocal(data.delivery);
            }

            if (data.payment && typeof data.payment === "object") {
                this.serverPayment = data.payment;
                this.paymentInfo = mapPaymentToLocal(data.payment);
            }

            const pricingStore = useCheckoutPricingStore();
            pricingStore.applyServerSnapshot(data.cart ?? null);
            if (Object.prototype.hasOwnProperty.call(data, "delivery_pricing")) {
                pricingStore.applyDeliveryPricingSnapshot(data.delivery_pricing);
            }
            if (Object.prototype.hasOwnProperty.call(data, "benefits_progress")) {
                pricingStore.applyBenefitsProgressSnapshot(data.benefits_progress);
            }

            this.recomputeSuggestedStep();
            this.persistSession();

            emitDomainEvent(DOMAIN_EVENTS.CART_CHANGED, { items: this.cartItems });
        },

        recomputeSuggestedStep() {
            if (this.status !== "draft") {
                this.suggestedStep = null;
                return;
            }

            const client = this.serverClient;
            const isGuest = client?.kind === "guest" || client == null;

            if (isGuest) {
                const name = String(this.guestContact.name || "").trim();
                const phone = String(this.guestContact.phone || "").trim();
                if (name === "" || phone === "") {
                    this.suggestedStep = "guest";
                    return;
                }
            } else if (client == null) {
                this.suggestedStep = "delivery";
                return;
            }

            if (!this.serverDelivery) {
                this.suggestedStep = "delivery";
                return;
            }

            if (!this.serverPayment) {
                this.suggestedStep = "payment";
                return;
            }

            if (this.hasCartItems) {
                this.suggestedStep = "confirm";
            }
        },

        setSuggestedStep(step) {
            if (step && CHECKOUT_WIZARD_STEPS.includes(step)) {
                this.suggestedStep = step;
            } else if (step === null) {
                this.suggestedStep = null;
            }
        },

        persistSession() {
            if (!this.checkoutId || this.status !== "draft") {
                return;
            }

            persistCheckoutSession(buildCheckoutSessionSnapshot(this));
        },

        tryRestoreSession() {
            return tryRestoreCheckoutSession(this);
        },

        ensureDraftCheckout() {
            return ensureDraftCheckout(this);
        },

        bootstrapSession() {
            return bootstrapCheckoutSession(this);
        },

        clearAfterCompleted() {
            this.checkoutId = null;
            this.status = null;
            this.cartItems = [];
            this.itemsTotalRubles = 0;
            this.serverClient = null;
            this.serverDelivery = null;
            this.serverPayment = null;
            this.clearLocalForms();
            this.suggestedStep = null;
            this.sessionReady = false;
            this.error = null;
            clearCheckoutSessionPayload();
        },

        clearLocalForms() {
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
            this.recomputeSuggestedStep();
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
            this.deliveryInfo = {
                ...this.deliveryInfo,
                address: {
                    ...(this.deliveryInfo.address &&
                    typeof this.deliveryInfo.address === "object"
                        ? this.deliveryInfo.address
                        : {}),
                    ...(partial || {}),
                },
            };
            this.recomputeSuggestedStep();
        },

        updateCartLine(productId, quantity, payload = null) {
            return updateCheckoutCartLine(this, productId, quantity, payload);
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

        flushToServer(options = {}) {
            return flushCheckoutToServer(this, options);
        },

        confirmCheckout() {
            return confirmCheckoutOnServer(this);
        },

        setPromotionGift(productId) {
            return setCheckoutPromotionGift(this, productId);
        },
    },
});
