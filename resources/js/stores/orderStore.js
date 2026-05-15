import { defineStore } from "pinia";
import { DOMAIN_EVENTS, emitDomainEvent } from "../shared/domainEvents";
import { buildCreateOrderPayloadDto } from "../api/orderContracts";
import { createOrderRequest, fetchOrdersRequest } from "../api/orderApi";

export const useOrderStore = defineStore("order", {
    state: () => ({
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
        buildCreateOrderPayload(selectedAddress, cartItems, intent, { isGuest = false } = {}) {
            return buildCreateOrderPayloadDto({
                selectedAddress,
                cartItems: Array.isArray(cartItems) ? cartItems : [],
                deliveryInfo: intent.deliveryInfo,
                paymentInfo: intent.paymentInfo,
                customerComment: intent.customerComment,
                guestContact:
                    isGuest &&
                    intent.guestContact &&
                    String(intent.guestContact.phone || "").trim() !== ""
                        ? intent.guestContact
                        : null,
                serverCartOnly: true,
            });
        },
        async createOrder(selectedAddress, cartItems, intent, { isGuest = false } = {}) {
            this.loading.create = true;
            this.error.create = null;

            try {
                const payload = this.buildCreateOrderPayload(
                    selectedAddress,
                    cartItems,
                    intent,
                    { isGuest },
                );
                const data = await createOrderRequest(payload);

                const createdOrder = data?.data ?? data ?? null;
                this.lastCreatedOrder = createdOrder;

                if (createdOrder) {
                    this.orders = [createdOrder, ...this.orders];
                }

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
