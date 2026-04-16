import { computed } from "vue";
import { useOrdersReadModel } from "./useOrdersReadModel";

export function useOrderHistoryReadModel({ autoload = true } = {}) {
    const { orders, refresh, loading, error } = useOrdersReadModel({ autoload });

    return {
        orders,
        refresh,
        loading: computed(() => loading.value),
        error: computed(() => error.value),
    };
}
