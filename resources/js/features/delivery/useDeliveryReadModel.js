import { computed, onMounted } from "vue";
import { toDeliveryFactsView } from "../../domain/delivery/deliveryMappers";
import { useDeliveryStore } from "../../stores/deliveryStore";

export function useDeliveryReadModel({ autoload = true } = {}) {
    const deliveryStore = useDeliveryStore();

    if (autoload) {
        onMounted(() => {
            void deliveryStore.fetchAll();
        });
    }

    const data = computed(() => deliveryStore.data);
    const settings = computed(() => deliveryStore.data?.settings ?? null);
    const zone = computed(() => deliveryStore.data?.zone ?? null);
    const facts = computed(() => toDeliveryFactsView(deliveryStore.data));

    const loading = computed(() => deliveryStore.loading);
    const error = computed(() => deliveryStore.error);

    return {
        data,
        settings,
        zone,
        facts,
        loading,
        error,
        refresh: () => deliveryStore.fetchAll(),
    };
}
