import { defineStore } from "pinia";
import { DOMAIN_EVENTS, emitDomainEvent } from "../shared/domainEvents";
import { buildCreateOrderPayloadDto } from "../api/orderContracts";
import { createOrderRequest, fetchOrdersRequest } from "../api/orderApi";
import { patchCheckoutDraftRequest } from "../api/shoppingApi";
import { applyShoppingSnapshotToStores } from "../features/shopping/shoppingApplySnapshot";

const ORDER_STORAGE_KEY = "gangsters_order_draft";

export const useOrderStore = defineStore("order", {
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
        orders: [],
        lastCreatedOrder: null,
        loading: {
            create: false,
            list: false,
        },
        error: {
            create: null,
            list: null,
        },
    }),
    getters: {
        /**
         * Сводка по всем заказам клиента из загруженного списка API (любой статус).
         */
        clientOrderStats(state) {
            const list = Array.isArray(state.orders) ? state.orders : [];
            let totalOrderSpendRubles = 0;
            let lastOrderAt = null;

            for (const o of list) {
                const t = Number(o.total);
                if (!Number.isNaN(t)) {
                    totalOrderSpendRubles += t;
                }
                const ca = o.created_at;
                if (ca) {
                    const cur = new Date(ca).getTime();
                    if (!lastOrderAt || cur > new Date(lastOrderAt).getTime()) {
                        lastOrderAt = ca;
                    }
                }
            }

            const count = list.length;

            return {
                count,
                totalOrderSpendRubles,
                lastOrderAt,
                averageOrderRubles: count
                    ? Math.round((totalOrderSpendRubles / count) * 100) / 100
                    : 0,
            };
        },
    },
    actions: {
        initFromStorage() {
            if (typeof window === "undefined") return;

            try {
                const raw = window.localStorage.getItem(ORDER_STORAGE_KEY);
                if (!raw) return;

                const parsed = JSON.parse(raw);
                if (!parsed || typeof parsed !== "object") return;

                if (parsed.deliveryInfo && typeof parsed.deliveryInfo === "object") {
                    this.deliveryInfo = {
                        ...this.deliveryInfo,
                        ...parsed.deliveryInfo,
                    };
                }

                if (parsed.paymentInfo && typeof parsed.paymentInfo === "object") {
                    this.paymentInfo = {
                        ...this.paymentInfo,
                        ...parsed.paymentInfo,
                    };
                }

                if (typeof parsed.customerComment === "string") {
                    this.customerComment = parsed.customerComment;
                }

                if (parsed.guestContact && typeof parsed.guestContact === "object") {
                    this.guestContact = {
                        name:
                            typeof parsed.guestContact.name === "string"
                                ? parsed.guestContact.name
                                : "",
                        phone:
                            typeof parsed.guestContact.phone === "string"
                                ? parsed.guestContact.phone
                                : "",
                        email:
                            typeof parsed.guestContact.email === "string"
                                ? parsed.guestContact.email
                                : "",
                    };
                }

                if (parsed.promotions && typeof parsed.promotions === "object") {
                    this.promotions = {
                        freeRollGiftProductId:
                            parsed.promotions.freeRollGiftProductId != null
                                ? Number(parsed.promotions.freeRollGiftProductId) || null
                                : null,
                    };
                }
            } catch (e) {
                console.error("Failed to init order store from localStorage", e);
            }
        },
        async persistDraft() {
            const body = {
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
                    email: this.guestContact.email,
                },
                customer_comment: this.customerComment,
                promotions: {
                    free_roll_gift_product_id: this.promotions.freeRollGiftProductId,
                },
            };
            try {
                const data = await patchCheckoutDraftRequest(body);
                applyShoppingSnapshotToStores(data);
            } catch (e) {
                console.error("persistDraft / shopping checkout", e);
            }
        },

        applyCheckoutDraftFromServer(draft) {
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
        clearDraft() {
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

            if (typeof window !== "undefined") {
                window.localStorage.removeItem(ORDER_STORAGE_KEY);
            }
        },
        setDeliveryInfo(payload) {
            this.deliveryInfo = {
                ...this.deliveryInfo,
                ...(payload || {}),
            };
            this.persistDraft();
        },
        setPaymentInfo(payload) {
            this.paymentInfo = {
                ...this.paymentInfo,
                ...(payload || {}),
            };
            this.persistDraft();
        },
        setCustomerComment(comment) {
            this.customerComment = comment || "";
            this.persistDraft();
        },
        setGuestContact(payload) {
            this.guestContact = {
                ...this.guestContact,
                ...(payload || {}),
            };
            this.persistDraft();
        },
        setPromotionGift(productId) {
            this.promotions = {
                freeRollGiftProductId:
                    productId != null ? Number(productId) || null : null,
            };
            return this.persistDraft();
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
            this.persistDraft();
        },
        setOrders(orders) {
            this.orders = Array.isArray(orders) ? orders : [];
        },
        async fetchOrders() {
            this.loading.list = true;
            this.error.list = null;

            try {
                const data = await fetchOrdersRequest();

                const orders = Array.isArray(data?.data ?? data) ? data.data ?? data : [];
                this.setOrders(orders);

                return orders;
            } catch (e) {
                console.error("Failed to fetch orders", e);
                this.error.list =
                    e?.response?.data?.message ||
                    "Не удалось загрузить заказы. Попробуйте позже.";
                throw e;
            } finally {
                this.loading.list = false;
            }
        },
        buildCreateOrderPayload(selectedAddress, cartItems, { isGuest = false } = {}) {
            return buildCreateOrderPayloadDto({
                selectedAddress,
                cartItems: Array.isArray(cartItems) ? cartItems : [],
                deliveryInfo: this.deliveryInfo,
                paymentInfo: this.paymentInfo,
                customerComment: this.customerComment,
                guestContact:
                    isGuest &&
                    this.guestContact &&
                    String(this.guestContact.phone || "").trim() !== ""
                        ? this.guestContact
                        : null,
                serverCartOnly: true,
            });
        },
        async createOrder(selectedAddress, cartItems, { isGuest = false } = {}) {
            this.loading.create = true;
            this.error.create = null;

            try {
                const payload = this.buildCreateOrderPayload(selectedAddress, cartItems, {
                    isGuest,
                });
                const data = await createOrderRequest(payload);

                const createdOrder = data?.data ?? data ?? null;
                this.lastCreatedOrder = createdOrder;

                if (createdOrder) {
                    this.orders = [createdOrder, ...this.orders];
                }

                this.clearDraft();
                emitDomainEvent(DOMAIN_EVENTS.ORDER_CREATED, {
                    order: createdOrder,
                });

                return createdOrder;
            } catch (e) {
                console.error("Failed to create order", e);
                this.error.create =
                    e?.response?.data?.message ||
                    "Не удалось оформить заказ. Попробуйте ещё раз.";
                throw e;
            } finally {
                this.loading.create = false;
            }
        },
    },
});

