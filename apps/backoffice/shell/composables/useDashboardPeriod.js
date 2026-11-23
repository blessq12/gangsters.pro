import { reactive } from "vue";

/**
 * Пресеты периода как в финансах: сегодня / 7 / 30 (+ from/to).
 */
export function useDashboardPeriod() {
    const filters = reactive({
        from: "",
        to: "",
    });

    const presets = [
        { key: "today", label: "Сегодня" },
        { key: "7d", label: "7 дней" },
        { key: "30d", label: "30 дней" },
    ];

    function pad(n) {
        return String(n).padStart(2, "0");
    }

    /**
     * @param {Date} date
     */
    function toDateInput(date) {
        return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
    }

    /**
     * @param {"today"|"7d"|"30d"} key
     */
    function applyPreset(key) {
        const now = new Date();
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        if (key === "today") {
            filters.from = toDateInput(today);
            filters.to = toDateInput(today);
            return;
        }
        if (key === "7d") {
            const from = new Date(today);
            from.setDate(from.getDate() - 6);
            filters.from = toDateInput(from);
            filters.to = toDateInput(today);
            return;
        }
        const from = new Date(today);
        from.setDate(from.getDate() - 29);
        filters.from = toDateInput(from);
        filters.to = toDateInput(today);
    }

    function queryParams() {
        return {
            from: filters.from || undefined,
            to: filters.to || undefined,
        };
    }

    return {
        filters,
        presets,
        applyPreset,
        queryParams,
        toDateInput,
    };
}
