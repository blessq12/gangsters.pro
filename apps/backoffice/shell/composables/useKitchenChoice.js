import { ref } from "vue";
import { getCompany } from "../../api/company.js";
import { kitchenLabel } from "../domain/orderStage.js";

const STORAGE_KEY = "gangsters_backoffice_kitchen_id";

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
        /* выбор кухни переживёт только эту вкладку */
    }
}

export function useKitchenChoice() {
    const kitchens = ref([]);
    const kitchenId = ref(readStored());
    const loading = ref(false);
    const error = ref("");

    function select(id) {
        kitchenId.value = id ? String(id) : "";
        writeStored(kitchenId.value);
    }

    async function load() {
        loading.value = true;
        error.value = "";
        try {
            const payload = await getCompany();
            kitchens.value = Array.isArray(payload?.kitchens) ? payload.kitchens : [];
            const known = kitchens.value.some((kitchen) => String(kitchen.id) === kitchenId.value);
            if (!known) {
                select(kitchens.value.length === 1 ? String(kitchens.value[0].id) : "");
            }
        } catch (err) {
            error.value = err instanceof Error ? err.message : "Не удалось загрузить кухни";
        } finally {
            loading.value = false;
        }
    }

    function labelOf(id) {
        const kitchen = kitchens.value.find((item) => String(item.id) === String(id));
        return kitchen ? kitchenLabel(kitchen) : (id ? `Кухня #${id}` : "—");
    }

    return { kitchens, kitchenId, loading, error, load, select, labelOf, kitchenLabel };
}
