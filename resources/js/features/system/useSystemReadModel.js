import { computed, onMounted } from "vue";
import { useSystemStore } from "../../stores/systemStore";

export function useSystemReadModel({ autoload = true } = {}) {
    const systemStore = useSystemStore();

    if (autoload) {
        onMounted(() => {
            void systemStore.fetchAll();
        });
    }

    const banners = computed(() => systemStore.banners);
    const promotions = computed(() => systemStore.promotions);

    const loading = computed(() => ({
        banners: systemStore.loadingBanners,
        promotions: systemStore.loadingPromotions,
    }));

    const errors = computed(() => ({
        banners: systemStore.errorBanners,
        promotions: systemStore.errorPromotions,
    }));

    return {
        banners,
        promotions,
        loading,
        errors,
        refreshAll: () => systemStore.fetchAll(),
        refreshBanners: () => systemStore.fetchBanners(),
        refreshPromotions: () => systemStore.fetchPromotions(),
    };
}
