import { defineStore } from "pinia";
import { fetchDeliveryData } from "../services/delivery/deliveryService";
import { mapApiError } from "../utils/api/mapApiError";

export const useDeliveryStore = defineStore("delivery", {
    state: () => ({
        data: null,
        loading: false,
        error: null,
    }),
    actions: {
        async fetchAll() {
            this.loading = true;
            this.error = null;

            try {
                this.data = await fetchDeliveryData();
            } catch (e) {
                console.error("Failed to fetch delivery data", e);
                this.error = mapApiError(
                    e,
                    "Не удалось загрузить данные доставки.",
                );
            } finally {
                this.loading = false;
            }
        },
    },
});
