import { defineStore } from "pinia";
import {
    normalizeCompanyDocument,
    normalizeCompanyLegal,
    normalizeCompanyProfile,
} from "../domain/company/companyMappers";

/**
 * Company slice of Content BC — filled by content bootstrap.
 */
export const useCompanyStore = defineStore("company", {
    state: () => ({
        profile: null,
        legal: null,
        documents: [],
    }),
    actions: {
        applyBootstrap(companyPayload) {
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
    },
});
