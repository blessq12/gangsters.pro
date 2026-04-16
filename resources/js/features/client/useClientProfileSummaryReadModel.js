import { computed } from "vue";
import { useClientOrderSummaryReadModel } from "../orders/useClientOrderSummaryReadModel";

export function useClientProfileSummaryReadModel(options) {
    const { stats, refresh, loading, error } =
        useClientOrderSummaryReadModel(options);

    return {
        stats: computed(() => stats.value),
        refresh,
        loading,
        error,
    };
}
