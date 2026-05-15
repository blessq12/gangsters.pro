import { defineStore } from "pinia";
import { patchCheckoutDraftRequest } from "../api/shoppingApi";
import { applyShoppingSnapshotToStores } from "../features/shopping/shoppingApplySnapshot";

const CHECKOUT_STEPS = ["cart", "guest", "delivery", "payment", "confirm"];

export const useCheckoutIntentStore = defineStore("checkoutIntent", {
    state: () => ({
        deliveryInfo: {
            method: "courier",
            address: null,
            comment: "",
            scheduledAt: null,
        },
        paymentInfo: {
            method: "card",
            changeFrom: null,
        },
        customerComment: "",
        guestContact: {
            name: "",
            phone: "",
            email: "",
        },
        promotions: {
            freeRollGiftProductId: null,
        },
        suggestedStep: null,
        flushing: false,
    }),
    actions: {
        applyFromServer(draft) {
            if (!draft || typeof draft !== "object") {
                return;
            }
            const di = draft.delivery_info;
            if (di && typeof di === "object") {
                this.deliveryInfo = {
                    method: di.method ?? this.deliveryInfo.method,
                    address: di.address ?? null,
                    comment: di.comment ?? "",
                    scheduledAt: di.scheduled_at ?? null,
                };
            }
            const pi = draft.payment_info;
            if (pi && typeof pi === "object") {
                this.paymentInfo = {
                    method: pi.method ?? this.paymentInfo.method,
                    changeFrom: pi.change_from ?? null,
                };
            }
            const gc = draft.guest_contact;
            if (gc && typeof gc === "object") {
                this.guestContact = {
                    name: typeof gc.name === "string" ? gc.name : "",
                    phone: typeof gc.phone === "string" ? gc.phone : "",
                    email: typeof gc.email === "string" ? gc.email : "",
                };
            }
            if (typeof draft.customer_comment === "string") {
                this.customerComment = draft.customer_comment;
            }
            const promotions = draft.promotions;
            if (promotions && typeof promotions === "object") {
                this.promotions = {
                    freeRollGiftProductId:
                        promotions.free_roll_gift_product_id != null
                            ? Number(promotions.free_roll_gift_product_id) || null
                            : null,
                };
            }
        },
        setSuggestedStep(step) {
            if (step && CHECKOUT_STEPS.includes(step)) {
                this.suggestedStep = step;
            } else if (step === null) {
                this.suggestedStep = null;
            }
        },
        toServerPayload() {
            return {
                delivery_info: {
                    method: this.deliveryInfo.method,
                    address: this.deliveryInfo.address,
                    comment: this.deliveryInfo.comment,
                    scheduled_at: this.deliveryInfo.scheduledAt,
                },
                payment_info: {
                    method: this.paymentInfo.method,
                    change_from: this.paymentInfo.changeFrom,
                },
                guest_contact: {
                    name: this.guestContact.name,
                    phone: this.guestContact.phone,
                },
                customer_comment: this.customerComment,
                promotions: {
                    free_roll_gift_product_id: this.promotions.freeRollGiftProductId,
                },
            };
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
            this.patchLocal({ paymentInfo: payload || {} });
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
        },
        async setPromotionGift(productId) {
            this.patchLocal({
                promotions: {
                    freeRollGiftProductId:
                        productId != null ? Number(productId) || null : null,
                },
            });
            return this.flushToServer();
        },
        async flushToServer() {
            this.flushing = true;
            try {
                const data = await patchCheckoutDraftRequest(this.toServerPayload());
                applyShoppingSnapshotToStores(data);
            } catch (e) {
                console.error("flushToServer / shopping checkout", e);
                throw e;
            } finally {
                this.flushing = false;
            }
        },
        clearLocal() {
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
            this.customerComment = "";
            this.guestContact = { name: "", phone: "", email: "" };
            this.promotions = { freeRollGiftProductId: null };
        },
    },
});
