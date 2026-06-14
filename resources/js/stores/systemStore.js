import { defineStore } from "pinia";
import {
    fetchSystemBannersRequest,
    fetchSystemPromotionsRequest,
} from "../api/systemApi";
import { mapApiError } from "../utils/api/mapApiError";

export const useSystemStore = defineStore("system", {
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
                const payload = await fetchSystemBannersRequest();
                const data = Array.isArray(payload?.data) ? payload.data : [];
                this.banners = data;
            } catch (e) {
                console.error("Failed to fetch banners", e);
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
                const payload = await fetchSystemPromotionsRequest();
                const data = Array.isArray(payload?.data) ? payload.data : [];
                this.promotions = data;
            } catch (e) {
                console.error("Failed to fetch promotions", e);
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
