import { ref } from "vue";
import { listBranches } from "../../api/company.js";
import { branchLabel } from "../domain/orderStage.js";

const STORAGE_KEY = "gangsters_backoffice_branch_id";

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

export function useBranchChoice() {
    const branches = ref([]);
    const branchId = ref(readStored());
    const loading = ref(false);
    const error = ref("");

    function select(id) {
        branchId.value = id ? String(id) : "";
        writeStored(branchId.value);
    }

    async function load() {
        loading.value = true;
        error.value = "";
        try {
            const payload = await listBranches({ offset: 0, limit: 100 });
            branches.value = Array.isArray(payload?.items) ? payload.items : [];
            const known = branches.value.some((branch) => String(branch.id) === branchId.value);
            if (!known) {
                select(branches.value.length === 1 ? String(branches.value[0].id) : "");
            }
        } catch (err) {
            error.value = err instanceof Error ? err.message : "Не удалось загрузить филиалы";
        } finally {
            loading.value = false;
        }
    }

    function labelOf(id) {
        const branch = branches.value.find((item) => String(item.id) === String(id));
        return branch ? branchLabel(branch) : (id ? `Филиал #${id}` : "—");
    }

    return { branches, branchId, loading, error, load, select, labelOf, branchLabel };
}
