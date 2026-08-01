import { defineStore } from "pinia";
import { fetchContentBootstrapRequest } from "../api/contentApi";
import { useCompanyStore } from "./companyStore";
import { useDeliveryStore } from "./deliveryStore";
import { useMarketingStore } from "./marketingStore";
import { mapApiError } from "../utils/api/mapApiError";

/**
 * Content bootstrap: company + marketing + delivery settings.
 */
export const useContentStore = defineStore("content", {
    state: () => ({
        version: null,
        loaded: false,
        loading: false,
        error: null,
        /** @type {Promise<void> | null} */
        inflight: null,
    }),
    actions: {
        applyBootstrap(payload) {
            if (!payload || typeof payload !== "object") {
                return;
            }

            if (payload.version != null) {
                this.version = payload.version;
            }

            const companyStore = useCompanyStore();
            const marketingStore = useMarketingStore();
            const deliveryStore = useDeliveryStore();

            if (payload.company != null) {
                companyStore.applyBootstrap(payload.company);
            }
            if (payload.marketing != null) {
                marketingStore.applyBootstrap(payload.marketing);
            }
            if (Object.prototype.hasOwnProperty.call(payload, "delivery")) {
                deliveryStore.applyBootstrap(payload.delivery);
            }
        },

        async fetchBootstrap() {
            if (this.inflight) {
                return this.inflight;
            }

            this.inflight = this.revalidateBootstrap();
            return this.inflight;
        },

        async revalidateBootstrap() {
            this.loading = true;
            this.error = null;

            try {
                const payload = await fetchContentBootstrapRequest();
                this.applyBootstrap(payload);
                this.loaded = true;
            } catch (e) {
                console.error("Failed to fetch content bootstrap", e);

                if (!this.loaded) {
                    this.error = mapApiError(
                        e,
                        "Не удалось загрузить контент сайта.",
                    );
                    throw e;
                }
            } finally {
                this.loading = false;
                this.inflight = null;
            }
        },
    },
});
