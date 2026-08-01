import { computed } from "vue";
import { toDeliveryFactsView } from "../../domain/delivery/deliveryMappers";
import { useContentStore } from "../../stores/contentStore";
import { useDeliveryStore } from "../../stores/deliveryStore";

export function useDeliveryReadModel(_options = {}) {
    const deliveryStore = useDeliveryStore();
    const contentStore = useContentStore();

    const data = computed(() => deliveryStore.data);
    const settings = computed(() => deliveryStore.data?.settings ?? null);
    const zone = computed(() => deliveryStore.data?.zone ?? null);
    const facts = computed(() => toDeliveryFactsView(deliveryStore.data));

    const loading = computed(
        () => contentStore.loading && !deliveryStore.data,
    );
    const error = computed(() => contentStore.error);

    return {
        data,
        settings,
        zone,
        facts,
        loading,
        error,
        refresh: () => contentStore.fetchBootstrap(),
    };
}
