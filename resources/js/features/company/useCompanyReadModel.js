import { computed, onMounted } from "vue";
import { useCompanyStore } from "../../stores/companyStore";

export function useCompanyReadModel({ autoload = true } = {}) {
    const companyStore = useCompanyStore();

    if (autoload) {
        onMounted(() => {
            void companyStore.fetchAll();
        });
    }

    const profile = computed(() => companyStore.profile);
    const legal = computed(() => companyStore.legal);
    const documents = computed(() => companyStore.documents);

    const loading = computed(() => ({
        profile: companyStore.loadingProfile,
        legal: companyStore.loadingLegal,
        documents: companyStore.loadingDocuments,
    }));

    const errors = computed(() => ({
        profile: companyStore.errorProfile,
        legal: companyStore.errorLegal,
        documents: companyStore.errorDocuments,
    }));

    return {
        profile,
        legal,
        documents,
        loading,
        errors,
        refreshAll: () => companyStore.fetchAll(),
        refreshProfile: () => companyStore.fetchProfile(),
        refreshLegal: () => companyStore.fetchLegal(),
        refreshDocuments: () => companyStore.fetchDocuments(),
    };
}
