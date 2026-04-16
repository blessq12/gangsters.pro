import { computed } from "vue";
import { useOrdersReadModel } from "./useOrdersReadModel";

export function useClientOrderSummaryReadModel({ autoload = true } = {}) {
    const { stats, refresh, loading, error } = useOrdersReadModel({ autoload });

    return {
        stats,
        refresh,
        loading: computed(() => loading.value),
        error: computed(() => error.value),
    };
}
