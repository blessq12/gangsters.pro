import { defineStore } from "pinia";

/**
 * Marketing slice of Content BC — filled by content bootstrap.
 */
export const useMarketingStore = defineStore("marketing", {
    state: () => ({
        banners: [],
        promotions: [],
    }),
    actions: {
        applyBootstrap(marketingPayload) {
            if (!marketingPayload || typeof marketingPayload !== "object") {
                return;
            }
            if (Array.isArray(marketingPayload.banners)) {
                this.banners = marketingPayload.banners;
            }
            if (Array.isArray(marketingPayload.promotions)) {
                this.promotions = marketingPayload.promotions;
            }
        },
    },
});
