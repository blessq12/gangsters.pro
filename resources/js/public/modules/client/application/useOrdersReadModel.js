import { computed, onMounted, onUnmounted } from "vue";
import { getClientAuthToken } from "../authToken";
import { useOrderStore } from "../store/orderStore";
import { useUserStore } from "../store/userStore";
import { subscribeDomainEvent, DOMAIN_EVENTS } from "../../../platform/domainEvents";

export function useOrdersReadModel({ autoload = false } = {}) {
    const orderStore = useOrderStore();
    const userStore = useUserStore();

    let unsubscribeOrderCreated = null;
    let unsubscribeClientLogout = null;
    let unsubscribeClientLogin = null;

    const stats = computed(() => orderStore.clientOrderStats);
    const orders = computed(() => orderStore.orders);
    const loading = computed(() => orderStore.loading.list);
    const error = computed(() => orderStore.error.list);

    function hasAuthToken() {
        return Boolean(userStore.token || getClientAuthToken());
    }

    async function refresh() {
        if (!hasAuthToken()) {
            orderStore.setOrders([]);
            return [];
        }

        return orderStore.fetchOrders();
    }

    onMounted(() => {
        if (autoload) {
            void refresh().catch(() => {});
        }

        unsubscribeOrderCreated = subscribeDomainEvent(
            DOMAIN_EVENTS.ORDER_CREATED,
            () => {
                void refresh().catch(() => {});
            },
        );
        unsubscribeClientLogin = subscribeDomainEvent(
            DOMAIN_EVENTS.CLIENT_LOGGED_IN,
            () => {
                void refresh().catch(() => {});
            },
        );
        unsubscribeClientLogout = subscribeDomainEvent(
            DOMAIN_EVENTS.CLIENT_LOGGED_OUT,
            () => {
                orderStore.setOrders([]);
            },
        );
    });

    onUnmounted(() => {
        if (unsubscribeOrderCreated) {
            unsubscribeOrderCreated();
            unsubscribeOrderCreated = null;
        }
        if (unsubscribeClientLogin) {
            unsubscribeClientLogin();
            unsubscribeClientLogin = null;
        }
        if (unsubscribeClientLogout) {
            unsubscribeClientLogout();
            unsubscribeClientLogout = null;
        }
    });

    return {
        stats,
        orders,
        loading,
        error,
        refresh,
    };
}
