import { defineStore } from "pinia";
import { httpClient } from "../../platform/httpClient";
import {
    normalizeCompanyDocument,
    normalizeCompanyLegal,
    normalizeCompanyProfile,
} from "./domain/mappers";
import { toDeliveryFactsView } from "./domain/mappers";
import { mapApiError } from "../../platform/mapApiError";

export async function fetchContentBootstrapRequest() {
    const response = await httpClient.get("/api/content");
    return response?.data ?? {};
}

/**
 * Content bootstrap: company + marketing + delivery (один store = один GET /api/content).
 */
export const useContentStore = defineStore("content", {
    state: () => ({
        version: null,
        loaded: false,
        loading: false,
        error: null,
        /** @type {Promise<void> | null} */
        inflight: null,

        profile: null,
        legal: null,
        documents: [],
        banners: [],
        promotions: [],
        delivery: null,
    }),
    getters: {
        deliveryFacts: (state) => toDeliveryFactsView(state.delivery),
    },
    actions: {
        applyCompany(companyPayload) {
            if (!companyPayload || typeof companyPayload !== "object") {
                return;
            }
            if (Object.prototype.hasOwnProperty.call(companyPayload, "main")) {
                this.profile = companyPayload.main
                    ? normalizeCompanyProfile(companyPayload.main)
                    : null;
            }
            if (Object.prototype.hasOwnProperty.call(companyPayload, "legals")) {
                this.legal = companyPayload.legals
                    ? normalizeCompanyLegal(companyPayload.legals)
                    : null;
            }
            if (Object.prototype.hasOwnProperty.call(companyPayload, "documents")) {
                this.documents = Array.isArray(companyPayload.documents)
                    ? companyPayload.documents
                          .map(normalizeCompanyDocument)
                          .filter(Boolean)
                    : [];
            }
        },

        applyMarketing(marketingPayload) {
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

        applyDelivery(deliveryPayload) {
            if (deliveryPayload == null) {
                return;
            }
            this.delivery = deliveryPayload;
        },

        applyBootstrap(payload) {
            if (!payload || typeof payload !== "object") {
                return;
            }

            if (payload.version != null) {
                this.version = payload.version;
            }
            if (payload.company != null) {
                this.applyCompany(payload.company);
            }
            if (payload.marketing != null) {
                this.applyMarketing(payload.marketing);
            }
            if (Object.prototype.hasOwnProperty.call(payload, "delivery")) {
                this.applyDelivery(payload.delivery);
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
