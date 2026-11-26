import { computed, unref } from "vue";
import { useOwnerBranchContext } from "../composables/useOwnerBranchContext.js";
import { useAuthStore } from "../store/authStore";
import { normalizeSearchRole } from "./types.js";

/**
 * Контекст роли и филиала для глобального поиска / меню.
 */
export function createSearchContext() {
    const auth = useAuthStore();
    const { selectedBranchId, ensureLoaded } = useOwnerBranchContext();

    const role = computed(() => normalizeSearchRole(auth.role || ""));

    const branchId = computed(() => {
        if (role.value === "owner") {
            const id = Number(selectedBranchId.value);
            return Number.isFinite(id) && id > 0 ? id : null;
        }
        const raw = auth.employee?.branch_id;
        const id = Number(raw);
        return Number.isFinite(id) && id > 0 ? id : null;
    });

    /**
     * @returns {Promise<void>}
     */
    async function ensureContext() {
        if (role.value === "owner") {
            await ensureLoaded();
        }
    }

    return {
        role,
        branchId,
        ensureContext,
        /**
         * @param {number} [limit]
         */
        toProviderContext(limit = 8) {
            return {
                role: unref(role),
                branchId: unref(branchId),
                limit,
            };
        },
    };
}
