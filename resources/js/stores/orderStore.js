import { defineStore } from "pinia";
import { buildCreateOrderPayloadDto } from "../api/orderContracts";
import { createOrderRequest, fetchOrdersRequest } from "../api/orderApi";

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
            } catch (e) {
                console.error("Failed to init order store from localStorage", e);
            }
        },
        persistDraft() {
            if (typeof window === "undefined") return;

            const payload = {
                deliveryInfo: this.deliveryInfo,
                paymentInfo: this.paymentInfo,
                customerComment: this.customerComment,
            };

            window.localStorage.setItem(ORDER_STORAGE_KEY, JSON.stringify(payload));
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
        buildCreateOrderPayload(client, selectedAddress, cartItems) {
            return buildCreateOrderPayloadDto({
                client,
                selectedAddress,
                cartItems: Array.isArray(cartItems) ? cartItems : [],
                deliveryInfo: this.deliveryInfo,
                paymentInfo: this.paymentInfo,
                customerComment: this.customerComment,
            });
        },
        async createOrder(client, selectedAddress, cartItems) {
            this.loading.create = true;
            this.error.create = null;

            try {
                const payload = this.buildCreateOrderPayload(
                    client,
                    selectedAddress,
                    cartItems,
                );
                const data = await createOrderRequest(payload);

                const createdOrder = data?.data ?? data ?? null;
                this.lastCreatedOrder = createdOrder;

                if (createdOrder) {
                    this.orders = [createdOrder, ...this.orders];
                }

                this.clearDraft();

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

