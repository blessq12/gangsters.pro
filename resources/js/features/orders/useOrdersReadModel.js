import { computed, onMounted, onUnmounted } from "vue";
import { useOrderStore } from "../../stores/orderStore";
import { subscribeDomainEvent, DOMAIN_EVENTS } from "../../shared/domainEvents";

export function useOrdersReadModel({ autoload = false } = {}) {
    const orderStore = useOrderStore();

    let unsubscribeOrderCreated = null;
    let unsubscribeClientLogout = null;

    const stats = computed(() => orderStore.clientOrderStats);
    const orders = computed(() => orderStore.orders);

    async function refresh() {
        return orderStore.fetchOrders();
    }

    onMounted(() => {
        if (autoload) {
            void refresh().catch(() => {});
        }

        unsubscribeOrderCreated = subscribeDomainEvent(DOMAIN_EVENTS.ORDER_CREATED, () => {
            void refresh().catch(() => {});
        });
        unsubscribeClientLogout = subscribeDomainEvent(DOMAIN_EVENTS.CLIENT_LOGGED_OUT, () => {
            orderStore.setOrders([]);
        });
    });

    onUnmounted(() => {
        if (unsubscribeOrderCreated) {
            unsubscribeOrderCreated();
            unsubscribeOrderCreated = null;
        }
        if (unsubscribeClientLogout) {
            unsubscribeClientLogout();
            unsubscribeClientLogout = null;
        }
    });

    return {
        orderStore,
        stats,
        orders,
        refresh,
    };
}
