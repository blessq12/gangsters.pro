import { computed, onMounted } from "vue";
import { useMarketingStore } from "../../stores/marketingStore";
import { isStorefrontBootstrapPending } from "../shell/isStorefrontBootstrapPending";
import { useStorefrontStore } from "../../stores/storefrontStore";

export function useMarketingReadModel({ autoload = true } = {}) {
    const marketingStore = useMarketingStore();
    const storefrontStore = useStorefrontStore();

    if (autoload) {
        onMounted(() => {
            if (isStorefrontBootstrapPending(storefrontStore)) {
                return;
            }

            void marketingStore.fetchAll();
        });
    }

    const banners = computed(() => marketingStore.banners);
    const promotions = computed(() => marketingStore.promotions);

    const loading = computed(() => ({
        banners: marketingStore.loadingBanners,
        promotions: marketingStore.loadingPromotions,
    }));

    const errors = computed(() => ({
        banners: marketingStore.errorBanners,
        promotions: marketingStore.errorPromotions,
    }));

    return {
        banners,
        promotions,
        loading,
        errors,
        refreshAll: () => marketingStore.fetchAll(),
        refreshBanners: () => marketingStore.fetchBanners(),
        refreshPromotions: () => marketingStore.fetchPromotions(),
    };
}
