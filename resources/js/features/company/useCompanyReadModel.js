import { computed } from "vue";
import { useCompanyStore } from "../../stores/companyStore";
import { useContentStore } from "../../stores/contentStore";

export function useCompanyReadModel(_options = {}) {
    const companyStore = useCompanyStore();
    const contentStore = useContentStore();

    const profile = computed(() => companyStore.profile);
    const legal = computed(() => companyStore.legal);
    const documents = computed(() => companyStore.documents);

    const loading = computed(() => ({
        profile: contentStore.loading && !companyStore.profile,
        legal: contentStore.loading && !companyStore.legal,
        documents: contentStore.loading && companyStore.documents.length === 0,
    }));

    const errors = computed(() => ({
        profile: contentStore.error,
        legal: contentStore.error,
        documents: contentStore.error,
    }));

    return {
        profile,
        legal,
        documents,
        loading,
        errors,
        refreshAll: () => contentStore.fetchBootstrap(),
        refreshProfile: () => contentStore.fetchBootstrap(),
        refreshLegal: () => contentStore.fetchBootstrap(),
        refreshDocuments: () => contentStore.fetchBootstrap(),
    };
}
