import { defineStore } from "pinia";
import { fetchCompany, fetchDelivery, fetchMarketing } from "../../api/content";
import { DOMAIN_EVENTS, emitDomainEvent } from "../../platform/domainEvents";
import {
    normalizeBranch,
    normalizeCompanyDocument,
    normalizeCompanyLegal,
    normalizeCompanyProfile,
} from "./domain/mappers";
import { toDeliveryFactsView } from "./domain/mappers";
import { mapApiError } from "../../platform/mapApiError";

const SELECTED_BRANCH_STORAGE_KEY = "gangsters_selected_branch_id";

function readStoredBranchId() {
    if (typeof window === "undefined") return null;

    try {
        const raw = window.localStorage.getItem(SELECTED_BRANCH_STORAGE_KEY);
        const id = Number(raw);
        return raw != null && Number.isInteger(id) && id > 0 ? id : null;
    } catch {
        return null;
    }
}

function writeStoredBranchId(branchId) {
    if (typeof window === "undefined") return;

    try {
        if (branchId == null) {
            window.localStorage.removeItem(SELECTED_BRANCH_STORAGE_KEY);
        } else {
            window.localStorage.setItem(SELECTED_BRANCH_STORAGE_KEY, String(branchId));
        }
    } catch (e) {
        console.error("Failed to persist selected branch id", e);
    }
}

/** Краткий филиал из GET /company → branches (без расписания). */
function toBranchSummary(apiBranch) {
    const id = Number(apiBranch?.id);
    if (!Number.isInteger(id) || id <= 0) {
        return null;
    }

    return {
        id,
        name: String(apiBranch.name ?? "").trim() || null,
        is_active: apiBranch.is_active !== false,
        phone: String(apiBranch.phone ?? "").trim() || null,
    };
}

/**
 * Выбор филиала: текущий (если ещё активен) → из localStorage (если активен) → первый активный.
 * @param {Array<{ id: number, is_active: boolean }>} branches
 * @param {number|null} currentId
 * @returns {number|null}
 */
function resolveSelectedBranchId(branches, currentId = null) {
    const activeIds = branches.filter((b) => b.is_active).map((b) => b.id);

    for (const candidate of [currentId, readStoredBranchId()]) {
        if (candidate != null && activeIds.includes(candidate)) {
            return candidate;
        }
    }

    return activeIds[0] ?? null;
}

/**
 * Content bootstrap: company (+ branches) → selectedBranchId → delivery(branch_id); marketing.
 * Расписание — только у branch, не у profile/company.
 */
export const useContentStore = defineStore("content", {
    state: () => ({
        loaded: false,
        loading: false,
        error: null,
        /** @type {Promise<void> | null} */
        inflight: null,
        /** @type {Promise<void> | null} */
        companyInflight: null,

        profile: null,
        /** @type {Array<{ id: number, name: string|null, is_active: boolean, phone: string|null }>} */
        branches: [],
        /** @type {number|null} */
        selectedBranchId: null,
        /** @type {object|null} подробности (расписание) для филиала из GET /company → branch */
        branchDetail: null,
        legal: null,
        documents: [],
        banners: [],
        promotions: [],
        delivery: null,
    }),
    getters: {
        activeBranches: (state) => state.branches.filter((b) => b.is_active),

        /** Выбранный филиал: телефон/название из списка, расписание — если пришло подробностями. */
        branch(state) {
            const summary = state.branches.find((b) => b.id === state.selectedBranchId);
            if (!summary) {
                return null;
            }

            const detail = state.branchDetail?.id === summary.id ? state.branchDetail : {};

            return normalizeBranch({ ...detail, ...summary });
        },

        deliveryFacts: (state) => toDeliveryFactsView(state.delivery),
        /** Расписание выбранного филиала */
        workSchedule() {
            return this.branch?.schedule ?? null;
        },
    },
    actions: {
        applyCompany(payload) {
            if (!payload || typeof payload !== "object") {
                return;
            }

            this.profile = payload.company ? normalizeCompanyProfile(payload.company) : null;
            this.legal = payload.legal ? normalizeCompanyLegal(payload.legal) : null;
            this.documents = Array.isArray(payload.documents)
                ? payload.documents.map(normalizeCompanyDocument).filter(Boolean)
                : [];
            this.branchDetail = payload.branch ? normalizeBranch(payload.branch) : null;

            this.branches = Array.isArray(payload.branches)
                ? payload.branches.map(toBranchSummary).filter(Boolean)
                : [];

            const resolvedId = resolveSelectedBranchId(this.branches, this.selectedBranchId);
            this.selectedBranchId = resolvedId;
            writeStoredBranchId(resolvedId);
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

        /** GET /company (с филиалами); параллельные вызовы делят один запрос. */
        loadCompany() {
            if (this.companyInflight) {
                return this.companyInflight;
            }

            this.companyInflight = fetchCompany()
                .then((payload) => this.applyCompany(payload ?? {}))
                .finally(() => {
                    this.companyInflight = null;
                });

            return this.companyInflight;
        },

        /** Дожидается списка филиалов и возвращает выбранный branch_id (или null). */
        async ensureSelectedBranchId() {
            if (this.selectedBranchId == null) {
                await this.loadCompany();
            }

            return this.selectedBranchId;
        },

        /** GET /delivery?branch_id= для выбранного филиала. */
        async loadDelivery() {
            const branchId = this.selectedBranchId;
            if (branchId == null) {
                this.delivery = null;
                return;
            }

            try {
                const payload = await fetchDelivery(branchId);
                if (this.selectedBranchId === branchId) {
                    this.delivery = payload ?? null;
                }
            } catch (e) {
                console.error("Failed to fetch delivery", e);
                if (this.selectedBranchId === branchId) {
                    this.delivery = null;
                }
            }
        },

        /** Смена филиала: localStorage → доставка/карта/телефон → событие для каталога. */
        async selectBranch(branchId) {
            const id = Number(branchId);
            const next = this.branches.find((b) => b.id === id && b.is_active);
            if (!next || next.id === this.selectedBranchId) {
                return;
            }

            this.selectedBranchId = next.id;
            this.delivery = null;
            writeStoredBranchId(next.id);
            emitDomainEvent(DOMAIN_EVENTS.BRANCH_CHANGED, { branchId: next.id });

            await this.loadDelivery();
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
                const [, marketing] = await Promise.all([this.loadCompany(), fetchMarketing()]);
                this.applyMarketing(marketing);
                await this.loadDelivery();
                this.loaded = true;
            } catch (e) {
                console.error("Failed to fetch content bootstrap", e);

                if (!this.loaded) {
                    this.error = mapApiError(e, "Не удалось загрузить контент сайта.");
                    throw e;
                }
            } finally {
                this.loading = false;
                this.inflight = null;
            }
        },
    },
});
