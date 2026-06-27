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

        applyDeferredBootstrap(deferredDelivery) {
            if (!deferredDelivery || typeof deferredDelivery !== "object") {
                return;
            }

            const geojson = deferredDelivery?.zone?.delivery_zone_geojson;
            if (geojson == null) {
                return;
            }

            if (!this.data || typeof this.data !== "object") {
                this.data = { settings: null, zone: { delivery_zone_geojson: geojson } };
                return;
            }

            this.data = {
                ...this.data,
                zone: {
                    ...(this.data.zone ?? {}),
                    delivery_zone_geojson: geojson,
                },
            };
        },
    },
});
