import { defineStore } from "pinia";

/**
 * Delivery settings slice of Content BC — filled by content bootstrap.
 */
export const useDeliveryStore = defineStore("delivery", {
    state: () => ({
        data: null,
    }),
    actions: {
        applyBootstrap(deliveryPayload) {
            if (deliveryPayload == null) {
                return;
            }

            this.data = deliveryPayload;
        },
    },
});
