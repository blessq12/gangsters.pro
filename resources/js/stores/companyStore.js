import { defineStore } from "pinia";
import {
    fetchCompanyDocuments,
    fetchCompanyLegal,
    fetchCompanyProfile,
} from "../services/company/companyService";
import { mapApiError } from "../utils/api/mapApiError";

export const useCompanyStore = defineStore("company", {
    state: () => ({
        profile: null,
        legal: null,
        documents: [],
        loadingProfile: false,
        loadingLegal: false,
        loadingDocuments: false,
        errorProfile: null,
        errorLegal: null,
        errorDocuments: null,
    }),
    actions: {
        async fetchProfile() {
            this.loadingProfile = true;
            this.errorProfile = null;

            try {
                this.profile = await fetchCompanyProfile();
            } catch (e) {
                console.error("Failed to fetch company profile", e);
                this.errorProfile = mapApiError(
                    e,
                    "Не удалось загрузить данные компании.",
                );
            } finally {
                this.loadingProfile = false;
            }
        },

        async fetchLegal() {
            this.loadingLegal = true;
            this.errorLegal = null;

            try {
                this.legal = await fetchCompanyLegal();
            } catch (e) {
                console.error("Failed to fetch company legal", e);
                this.errorLegal = mapApiError(
                    e,
                    "Не удалось загрузить юридические данные.",
                );
            } finally {
                this.loadingLegal = false;
            }
        },

        async fetchDocuments() {
            this.loadingDocuments = true;
            this.errorDocuments = null;

            try {
                this.documents = await fetchCompanyDocuments();
            } catch (e) {
                console.error("Failed to fetch company documents", e);
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
                this.fetchProfile(),
                this.fetchLegal(),
                this.fetchDocuments(),
            ]);
        },
    },
});
