import { computed, onMounted } from "vue";
import { useMarketingStore } from "../../stores/marketingStore";

export function useMarketingReadModel({ autoload = true } = {}) {
    const marketingStore = useMarketingStore();

    if (autoload) {
        onMounted(() => {
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
