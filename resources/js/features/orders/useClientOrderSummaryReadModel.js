import { computed } from "vue";
import { useOrdersReadModel } from "./useOrdersReadModel";

export function useClientOrderSummaryReadModel({ autoload = true } = {}) {
    const { stats, refresh, orderStore } = useOrdersReadModel({ autoload });

    return {
        stats,
        refresh,
        loading: computed(() => orderStore.loading.list),
        error: computed(() => orderStore.error.list),
    };
}
