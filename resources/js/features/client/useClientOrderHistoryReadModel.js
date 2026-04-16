import { computed } from "vue";
import { useOrderHistoryReadModel } from "../orders/useOrderHistoryReadModel";

export function useClientOrderHistoryReadModel(options) {
    const { orders, refresh, loading, error } = useOrderHistoryReadModel(options);

    return {
        orders: computed(() => orders.value),
        refresh,
        loading,
        error,
    };
}
