import { defineStore } from "pinia";
import {
    fetchSystemBannersRequest,
    fetchSystemPromotionsRequest,
    fetchSystemCompanyRequest,
    fetchSystemCompanyLegalRequest,
    fetchSystemDocumentsRequest,
} from "../api/systemApi";
import { mapApiError } from "../utils/api/mapApiError";

export const useSystemStore = defineStore("system", {
    state: () => ({
        banners: [],
        promotions: [],
        company: null,
        companyLegal: null,
        documents: [],
        loadingBanners: false,
        loadingPromotions: false,
        loadingCompany: false,
        loadingCompanyLegal: false,
        loadingDocuments: false,
        errorBanners: null,
        errorPromotions: null,
        errorCompany: null,
        errorCompanyLegal: null,
        errorDocuments: null,
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

        async fetchCompany() {
            this.loadingCompany = true;
            this.errorCompany = null;

            try {
                const payload = await fetchSystemCompanyRequest();
                this.company =
                    payload && typeof payload === "object"
                        ? payload.data ?? null
                        : null;
            } catch (e) {
                console.error("Failed to fetch company", e);
                this.errorCompany = mapApiError(
                    e,
                    "Не удалось загрузить данные компании.",
                );
            } finally {
                this.loadingCompany = false;
            }
        },

        async fetchCompanyLegal() {
            this.loadingCompanyLegal = true;
            this.errorCompanyLegal = null;

            try {
                const payload = await fetchSystemCompanyLegalRequest();
                this.companyLegal =
                    payload && typeof payload === "object"
                        ? payload.data ?? null
                        : null;
            } catch (e) {
                console.error("Failed to fetch company legal", e);
                this.errorCompanyLegal = mapApiError(
                    e,
                    "Не удалось загрузить юридические данные.",
                );
            } finally {
                this.loadingCompanyLegal = false;
            }
        },

        async fetchDocuments() {
            this.loadingDocuments = true;
            this.errorDocuments = null;

            try {
                const payload = await fetchSystemDocumentsRequest();
                const data = Array.isArray(payload?.data) ? payload.data : [];
                this.documents = data;
            } catch (e) {
                console.error("Failed to fetch documents", e);
                this.errorDocuments = mapApiError(
                    e,
                    "Не удалось загрузить документы.",
                );
            } finally {
                this.loadingDocuments = false;
            }
        },

        async fetchAll() {
            await Promise.allSettled([
                this.fetchBanners(),
                this.fetchPromotions(),
                this.fetchCompany(),
                this.fetchCompanyLegal(),
                this.fetchDocuments(),
            ]);
        },
    },
});
