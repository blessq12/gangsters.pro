import { defineStore } from "pinia";
import { fetchStorefrontBootstrapRequest } from "../api/storefrontApi";
import { useCatalogStore } from "./catalogStore";
import { useCompanyStore } from "./companyStore";
import { useDeliveryStore } from "./deliveryStore";
import { useMarketingStore } from "./marketingStore";
import { mapApiError } from "../utils/api/mapApiError";

export const useStorefrontStore = defineStore("storefront", {
    state: () => ({
        version: null,
        loaded: false,
        loading: false,
        error: null,
    }),
    actions: {
        async fetchBootstrap() {
            this.loading = true;
            this.error = null;

            try {
                const payload = await fetchStorefrontBootstrapRequest();
                this.version = payload?.version ?? null;

                const catalogStore = useCatalogStore();
                const deliveryStore = useDeliveryStore();
                const companyStore = useCompanyStore();
                const marketingStore = useMarketingStore();

                if (payload?.catalog?.categories) {
                    catalogStore.applyBootstrapCatalog(payload.catalog);
                }
                if (payload?.delivery != null) {
                    deliveryStore.data = payload.delivery;
                }
                if (payload?.company != null) {
                    companyStore.applyBootstrap(payload.company);
                }
                if (payload?.marketing != null) {
                    marketingStore.applyBootstrap(payload.marketing);
                }

                this.loaded = true;
            } catch (e) {
                console.error("Failed to fetch storefront bootstrap", e);
                this.error = mapApiError(
                    e,
                    "Не удалось загрузить данные приложения.",
                );
                throw e;
            } finally {
                this.loading = false;
            }
        },
    },
});
