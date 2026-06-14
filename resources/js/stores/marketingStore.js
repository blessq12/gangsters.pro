import { defineStore } from "pinia";
import {
    fetchMarketingBanners,
    fetchMarketingPromotions,
} from "../services/marketing/marketingService";
import { mapApiError } from "../utils/api/mapApiError";

export const useMarketingStore = defineStore("marketing", {
    state: () => ({
        banners: [],
        promotions: [],
        loadingBanners: false,
        loadingPromotions: false,
        errorBanners: null,
        errorPromotions: null,
    }),
    actions: {
        async fetchBanners() {
            this.loadingBanners = true;
            this.errorBanners = null;

            try {
                this.banners = await fetchMarketingBanners();
            } catch (e) {
                console.error("Failed to fetch marketing banners", e);
                this.errorBanners = mapApiError(
                    e,
                    "Не удалось загрузить баннеры.",
                );
            } finally {
                this.loadingBanners = false;
            }
        },

        async fetchPromotions() {
            this.loadingPromotions = true;
            this.errorPromotions = null;

            try {
                this.promotions = await fetchMarketingPromotions();
            } catch (e) {
                console.error("Failed to fetch marketing promotions", e);
                this.errorPromotions = mapApiError(
                    e,
                    "Не удалось загрузить акции.",
                );
            } finally {
                this.loadingPromotions = false;
            }
        },

        async fetchAll() {
            await Promise.allSettled([
                this.fetchBanners(),
                this.fetchPromotions(),
            ]);
        },
    },
});
