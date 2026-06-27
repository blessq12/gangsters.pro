import { computed, onMounted } from "vue";
import { toDeliveryFactsView } from "../../domain/delivery/deliveryMappers";
import { isStorefrontBootstrapPending } from "../shell/isStorefrontBootstrapPending";
import { useDeliveryStore } from "../../stores/deliveryStore";
import { useStorefrontStore } from "../../stores/storefrontStore";

export function useDeliveryReadModel({ autoload = true } = {}) {
    const deliveryStore = useDeliveryStore();
    const storefrontStore = useStorefrontStore();

    if (autoload) {
        onMounted(() => {
            if (isStorefrontBootstrapPending(storefrontStore)) {
                return;
            }

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
