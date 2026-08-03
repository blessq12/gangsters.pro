import { defineStore } from "pinia";
import { DOMAIN_EVENTS, emitDomainEvent } from "../../../platform/domainEvents";
import { fetchOrdersRequest } from "../api";

export const useOrderStore = defineStore("order", {
    state: () => ({
        orders: [],
        loading: {
            list: false,
        },
        error: {
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
    },
});
