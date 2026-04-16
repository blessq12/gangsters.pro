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
    const company = computed(() => systemStore.company);
    const companyLegal = computed(() => systemStore.companyLegal);
    const documents = computed(() => systemStore.documents);

    const loading = computed(() => ({
        banners: systemStore.loadingBanners,
        promotions: systemStore.loadingPromotions,
        company: systemStore.loadingCompany,
        companyLegal: systemStore.loadingCompanyLegal,
        documents: systemStore.loadingDocuments,
    }));

    const errors = computed(() => ({
        banners: systemStore.errorBanners,
        promotions: systemStore.errorPromotions,
        company: systemStore.errorCompany,
        companyLegal: systemStore.errorCompanyLegal,
        documents: systemStore.errorDocuments,
    }));

    return {
        banners,
        promotions,
        company,
        companyLegal,
        documents,
        loading,
        errors,
        refreshAll: () => systemStore.fetchAll(),
        refreshBanners: () => systemStore.fetchBanners(),
        refreshPromotions: () => systemStore.fetchPromotions(),
        refreshCompany: () => systemStore.fetchCompany(),
        refreshCompanyLegal: () => systemStore.fetchCompanyLegal(),
        refreshDocuments: () => systemStore.fetchDocuments(),
    };
}

