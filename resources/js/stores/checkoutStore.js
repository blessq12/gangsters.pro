import { defineStore } from "pinia";
import {
    confirmCheckoutRequest,
    createCheckoutRequest,
    fetchCheckoutRequest,
    setCheckoutClientRequest,
    setCheckoutDeliveryRequest,
    setCheckoutPaymentRequest,
    updateCheckoutCartRequest,
} from "../api/checkoutApi";
import {
    fromServerCheckoutPaymentMethod,
    normalizeCheckoutPaymentMethod,
    toServerCheckoutPaymentMethod,
} from "../features/checkout/checkoutPaymentMethods";
import { normalizeCheckoutCartBlock } from "../features/checkout/normalizeCheckoutCart";
import { DOMAIN_EVENTS, emitDomainEvent } from "../shared/domainEvents";
import { useCheckoutPricingStore } from "./checkoutPricingStore";

const CHECKOUT_STEPS = ["cart", "guest", "delivery", "payment", "confirm"];
const SESSION_KEY = "gangsters_checkout_session_v1";

function readSessionPayload() {
    if (typeof window === "undefined") {
        return null;
    }

    try {
        const raw = window.sessionStorage.getItem(SESSION_KEY);
        if (!raw) {
            return null;
        }
        const parsed = JSON.parse(raw);
        return parsed && typeof parsed === "object" ? parsed : null;
    } catch {
        return null;
    }
}

function writeSessionPayload(payload) {
    if (typeof window === "undefined") {
        return;
    }

    window.sessionStorage.setItem(SESSION_KEY, JSON.stringify(payload));
}

function clearSessionPayload() {
    if (typeof window === "undefined") {
        return;
    }

    window.sessionStorage.removeItem(SESSION_KEY);
}

function mapClientToGuestContact(client) {
    if (!client || typeof client !== "object") {
        return { name: "", phone: "", email: "" };
    }

    return {
        name: typeof client.name === "string" ? client.name : "",
        phone: typeof client.phone === "string" ? client.phone : "",
        email: typeof client.email === "string" ? client.email : "",
    };
}

function mapDeliveryToLocal(delivery) {
    if (!delivery || typeof delivery !== "object") {
        return {
            method: null,
            address: null,
            comment: "",
            scheduledAt: null,
        };
    }

    return {
        method: delivery.method ?? null,
        address: delivery.address ?? null,
        comment: delivery.comment ?? "",
        scheduledAt: delivery.scheduled_at ?? null,
    };
}

function mergeCheckoutDeliveryComment(deliveryComment, customerComment) {
    const parts = [
        String(deliveryComment || "").trim(),
        String(customerComment || "").trim(),
    ].filter(Boolean);

    return parts.length > 0 ? parts.join("\n\n") : "";
}

function mapPaymentToLocal(payment) {
    if (!payment || typeof payment !== "object") {
        return {
            method: null,
            changeFrom: null,
        };
    }

    return {
        method:
            payment.method != null
                ? fromServerCheckoutPaymentMethod(payment.method)
                : null,
        changeFrom: payment.change_from_rubles ?? null,
    };
}

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
            if (step && CHECKOUT_STEPS.includes(step)) {
                this.suggestedStep = step;
            } else if (step === null) {
                this.suggestedStep = null;
            }
        },

        persistSession() {
            if (!this.checkoutId || this.status !== "draft") {
                return;
            }

            const pricingStore = useCheckoutPricingStore();
            const deliveryPricing = pricingStore.deliveryPricing;
            const benefitsProgress = pricingStore.benefitsProgress;
            const promoState = pricingStore.promoState;

            writeSessionPayload({
                checkoutId: this.checkoutId,
                status: this.status,
                snapshot: {
                    checkout_id: this.checkoutId,
                    status: this.status,
                    cart: {
                        items: this.cartItems.map((item) => ({
                            product_id: item.productId,
                            product_name: item.productSnapshot?.name ?? "",
                            quantity: item.qty,
                            unit_price_rubles: item.productSnapshot?.price ?? 0,
                            line_total_rubles:
                                (Number(item.pricing?.lineTotalKopecks) || 0) / 100,
                            payload: item.payload ?? null,
                        })),
                        items_total_rubles: this.itemsTotalRubles,
                        promo_state: promoState,
                    },
                    client: this.serverClient,
                    delivery: this.serverDelivery,
                    payment: this.serverPayment,
                    delivery_pricing: deliveryPricing
                        ? {
                              method: deliveryPricing.method,
                              items_payable_kopecks: deliveryPricing.itemsPayableKopecks,
                              delivery_fee_kopecks: deliveryPricing.deliveryFeeKopecks,
                              is_free: deliveryPricing.isFree,
                              is_preview: deliveryPricing.isPreview,
                              remaining_to_free_kopecks: deliveryPricing.remainingToFreeKopecks,
                              items_total_kopecks: deliveryPricing.itemsTotalKopecks,
                              grand_total_kopecks: deliveryPricing.grandTotalKopecks,
                              items_total_rub: deliveryPricing.itemsTotalRub,
                              delivery_fee_rub: deliveryPricing.deliveryFeeRub,
                              grand_total_rub: deliveryPricing.grandTotalRub,
                          }
                        : null,
                    benefits_progress: benefitsProgress,
                },
            });
        },

        async tryRestoreSession() {
            const saved = readSessionPayload();
            if (!saved?.checkoutId || saved.status !== "draft") {
                return false;
            }

            try {
                const remote = await fetchCheckoutRequest(saved.checkoutId);
                if (remote?.status !== "draft") {
                    clearSessionPayload();
                    return false;
                }

                this.applyFromServer(remote);
                this.sessionReady = true;

                return true;
            } catch (error) {
                if (!saved.snapshot) {
                    return false;
                }

                this.checkoutId = saved.checkoutId;
                this.status = saved.status;
                this.applyFromServer(
                    saved.snapshot ?? { checkout_id: saved.checkoutId, status: "draft" },
                );
                this.sessionReady = true;

                return true;
            }
        },

        async ensureDraftCheckout() {
            if (this.hasCheckout) {
                return this.checkoutId;
            }

            const created = await createCheckoutRequest();
            this.applyFromServer(created);
            this.sessionReady = true;

            return this.checkoutId;
        },

        async bootstrapSession() {
            if (this.sessionReady) {
                return;
            }

            if (await this.tryRestoreSession()) {
                return;
            }

            await this.ensureDraftCheckout();
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
            clearSessionPayload();
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
            const patch = { ...(payload || {}) };
            if (patch.method != null) {
                patch.method = normalizeCheckoutPaymentMethod(patch.method);
            }
            this.patchLocal({ paymentInfo: patch });
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

        async updateCartLine(productId, quantity, payload = null) {
            await this.ensureDraftCheckout();
            this.loading = true;
            this.error = null;

            try {
                const body = {
                    product_id: Number(productId),
                    quantity: Number(quantity),
                };
                if (payload != null) {
                    body.payload = payload;
                }

                const data = await updateCheckoutCartRequest(this.checkoutId, body);
                this.applyFromServer(data);
                return data;
            } catch (e) {
                console.error("updateCartLine / checkout", e);
                this.error =
                    e?.response?.data?.message || "Не удалось обновить корзину.";
                throw e;
            } finally {
                this.loading = false;
            }
        },

        buildClientPayload({ clientId = null, isGuest = false } = {}) {
            if (clientId != null) {
                return {
                    client_id: Number(clientId),
                    name: this.guestContact.name || undefined,
                    phone: this.guestContact.phone || undefined,
                    email: this.guestContact.email || undefined,
                };
            }

            if (!isGuest) {
                return {
                    client_id: null,
                };
            }

            return {
                name: this.guestContact.name,
                phone: this.guestContact.phone,
                email: this.guestContact.email || undefined,
            };
        },

        buildDeliveryPayload(selectedAddress = null) {
            const method = this.deliveryInfo.method;
            let address = this.deliveryInfo.address;

            if (method === "courier" && selectedAddress && typeof selectedAddress === "object") {
                address = {
                    street: selectedAddress.street ?? "",
                    house: selectedAddress.house ?? "",
                    entrance: selectedAddress.entrance ?? null,
                    apartment: selectedAddress.apartment ?? null,
                };
            }

            return {
                method,
                address: method === "courier" ? address : null,
                comment:
                    mergeCheckoutDeliveryComment(
                        this.deliveryInfo.comment,
                        this.customerComment,
                    ) || undefined,
                scheduled_at: this.deliveryInfo.scheduledAt || undefined,
            };
        },

        buildPaymentPayload() {
            return {
                method: toServerCheckoutPaymentMethod(this.paymentInfo.method),
                change_from_rubles:
                    this.paymentInfo.changeFrom != null
                        ? Number(this.paymentInfo.changeFrom)
                        : undefined,
            };
        },

        async flushClientToServer({ clientId = null, isGuest = false } = {}) {
            await this.ensureDraftCheckout();
            this.flushing = true;

            try {
                const data = await setCheckoutClientRequest(
                    this.checkoutId,
                    this.buildClientPayload({ clientId, isGuest }),
                );
                this.applyFromServer(data);
            } catch (e) {
                console.error("flushClientToServer / checkout", e);
                throw e;
            } finally {
                this.flushing = false;
            }
        },

        async flushDeliveryToServer(selectedAddress = null) {
            await this.ensureDraftCheckout();
            this.flushing = true;

            try {
                const data = await setCheckoutDeliveryRequest(
                    this.checkoutId,
                    this.buildDeliveryPayload(selectedAddress),
                );
                this.applyFromServer(data);
            } catch (e) {
                console.error("flushDeliveryToServer / checkout", e);
                throw e;
            } finally {
                this.flushing = false;
            }
        },

        async flushPaymentToServer() {
            await this.ensureDraftCheckout();
            this.flushing = true;

            try {
                const data = await setCheckoutPaymentRequest(
                    this.checkoutId,
                    this.buildPaymentPayload(),
                );
                this.applyFromServer(data);
            } catch (e) {
                console.error("flushPaymentToServer / checkout", e);
                throw e;
            } finally {
                this.flushing = false;
            }
        },

        async flushToServer(options = {}) {
            const { clientId = null, isGuest = false, selectedAddress = null } = options;

            if (isGuest || clientId != null) {
                await this.flushClientToServer({ clientId, isGuest });
            }
            if (this.deliveryInfo.method) {
                await this.flushDeliveryToServer(selectedAddress);
            }
            if (this.paymentInfo.method) {
                await this.flushPaymentToServer();
            }
        },

        async confirmCheckout() {
            await this.ensureDraftCheckout();
            this.flushing = true;

            try {
                const data = await confirmCheckoutRequest(this.checkoutId);
                this.applyFromServer(data);
                clearSessionPayload();
                return data;
            } catch (e) {
                console.error("confirmCheckout", e);
                this.error =
                    e?.response?.data?.message || "Не удалось подтвердить оформление.";
                throw e;
            } finally {
                this.flushing = false;
            }
        },

        async setPromotionGift(productId) {
            const previousId = this.promotions.freeRollGiftProductId;
            const nextId = productId != null ? Number(productId) || null : null;

            if (previousId != null && previousId !== nextId) {
                await this.updateCartLine(previousId, 0, { kind: "gift" });
            }

            if (nextId != null) {
                await this.updateCartLine(nextId, 1, { kind: "gift" });
            }

            this.patchLocal({
                promotions: {
                    freeRollGiftProductId: nextId,
                },
            });
        },
    },
});

