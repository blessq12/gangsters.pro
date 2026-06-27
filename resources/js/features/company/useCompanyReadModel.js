import { computed, onMounted } from "vue";
import { useCompanyStore } from "../../stores/companyStore";
import { isStorefrontBootstrapPending } from "../shell/isStorefrontBootstrapPending";
import { useStorefrontStore } from "../../stores/storefrontStore";

export function useCompanyReadModel({ autoload = true } = {}) {
    const companyStore = useCompanyStore();
    const storefrontStore = useStorefrontStore();

    if (autoload) {
        onMounted(() => {
            if (isStorefrontBootstrapPending(storefrontStore)) {
                return;
            }

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
