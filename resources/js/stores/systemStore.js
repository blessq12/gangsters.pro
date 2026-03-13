import { defineStore } from "pinia";
import axios from "axios";

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
                const response = await axios.get("/api/system/banners");
                const data = Array.isArray(response.data?.data) ? response.data.data : [];
                this.banners = data;
            } catch (e) {
                console.error("Failed to fetch banners", e);
                this.errorBanners =
                    e?.response?.data?.message || "Не удалось загрузить баннеры.";
            } finally {
                this.loadingBanners = false;
            }
        },

        async fetchPromotions() {
            this.loadingPromotions = true;
            this.errorPromotions = null;

            try {
                const response = await axios.get("/api/system/promotions");
                const data = Array.isArray(response.data?.data) ? response.data.data : [];
                this.promotions = data;
            } catch (e) {
                console.error("Failed to fetch promotions", e);
                this.errorPromotions =
                    e?.response?.data?.message || "Не удалось загрузить акции.";
            } finally {
                this.loadingPromotions = false;
            }
        },

        async fetchAll() {
            await Promise.allSettled([this.fetchBanners(), this.fetchPromotions()]);
        },
    },
});

