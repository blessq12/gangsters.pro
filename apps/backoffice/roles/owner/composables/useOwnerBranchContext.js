import { computed, ref } from "vue";
import { listBranches } from "../../../api/company.js";
import { branchLabel } from "../../../shell/domain/orderStage.js";

const STORAGE_KEY = "gangsters_owner_branch_id";

function readStored() {
    if (typeof window === "undefined") {
        return "";
    }
    try {
        return window.localStorage.getItem(STORAGE_KEY) || "";
    } catch {
        return "";
    }
}

function writeStored(value) {
    if (typeof window === "undefined") {
        return;
    }
    try {
        if (value) {
            window.localStorage.setItem(STORAGE_KEY, value);
            return;
        }
        window.localStorage.removeItem(STORAGE_KEY);
    } catch {
        /* выбор филиала переживёт только эту вкладку */
    }
}

/** Общее состояние на все экраны owner: выбор филиала не сбрасывается при навигации. */
const branches = ref([]);
const selectedBranchId = ref(readStored());
const loading = ref(false);
const error = ref("");
const loaded = ref(false);

function select(id) {
    selectedBranchId.value = id ? String(id) : "";
    writeStored(selectedBranchId.value);
}

async function load() {
    loading.value = true;
    error.value = "";
    try {
        const payload = await listBranches({ offset: 0, limit: 100 });
        const rows = Array.isArray(payload?.items) ? payload.items : [];
        branches.value = rows.filter((branch) => branch.is_active !== false);
        loaded.value = true;
        const known = branches.value.some(
            (branch) => String(branch.id) === selectedBranchId.value,
        );
        if (!known) {
            select(branches.value.length === 1 ? String(branches.value[0].id) : "");
        }
    } catch (err) {
        error.value = err instanceof Error ? err.message : "Не удалось загрузить филиалы";
    } finally {
        loading.value = false;
    }
}

/** @type {Promise<void>|null} */
let pendingLoad = null;

/** Загружает список один раз за сессию; параллельные вызовы ждут один и тот же запрос. */
function ensureLoaded() {
    if (loaded.value) {
        return Promise.resolve();
    }
    if (pendingLoad === null) {
        pendingLoad = load().finally(() => {
            pendingLoad = null;
        });
    }
    return pendingLoad;
}

/**
 * Контекст филиала для owner-экранов (меню, доставка, акции).
 * Хранит выбранный филиал в localStorage и подгружает активные филиалы.
 */
export function useOwnerBranchContext() {
    const selectedBranch = computed(() =>
        branches.value.find((branch) => String(branch.id) === selectedBranchId.value) ?? null,
    );

    return {
        branches,
        selectedBranchId,
        selectedBranch,
        loading,
        error,
        load,
        ensureLoaded,
        select,
        branchLabel,
    };
}
