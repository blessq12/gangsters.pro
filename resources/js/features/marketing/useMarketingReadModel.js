import { computed } from "vue";
import { useMarketingStore } from "../../stores/marketingStore";
import { useContentStore } from "../../stores/contentStore";

export function useMarketingReadModel(_options = {}) {
    const marketingStore = useMarketingStore();
    const contentStore = useContentStore();

    const banners = computed(() => marketingStore.banners);
    const promotions = computed(() => marketingStore.promotions);

    const loading = computed(() => ({
        banners: contentStore.loading && marketingStore.banners.length === 0,
        promotions:
            contentStore.loading && marketingStore.promotions.length === 0,
    }));

    const errors = computed(() => ({
        banners: contentStore.error,
        promotions: contentStore.error,
    }));

    return {
        banners,
        promotions,
        loading,
        errors,
        refreshAll: () => contentStore.fetchBootstrap(),
        refreshBanners: () => contentStore.fetchBootstrap(),
        refreshPromotions: () => contentStore.fetchBootstrap(),
    };
}
