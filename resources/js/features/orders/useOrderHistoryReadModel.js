import { computed } from "vue";
import { useOrdersReadModel } from "./useOrdersReadModel";

export function useOrderHistoryReadModel({ autoload = true } = {}) {
    const { orders, refresh, orderStore } = useOrdersReadModel({ autoload });

    return {
        orders,
        refresh,
        loading: computed(() => orderStore.loading.list),
        error: computed(() => orderStore.error.list),
    };
}
