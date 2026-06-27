import { computed } from "vue";
import { storeToRefs } from "pinia";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import { useUserStore } from "../../stores/userStore";
import { useCheckoutStore } from "../../stores/checkoutStore";
import { useOrderPreview } from "./useOrderPreview";

import { CHECKOUT_LOADING_LABELS } from "./checkoutLoadingLabels";

export const DELIVERY_ZONE_PHASE = Object.freeze({
    HIDDEN: "hidden",
    PICKUP: "pickup",
    IDLE: "idle",
    PENDING: "pending",
    IN_ZONE: "in_zone",
    OUT_OF_ZONE: "out_of_zone",
    UNKNOWN: "unknown",
});

export const DELIVERY_ZONE_MESSAGES = Object.freeze({
    [DELIVERY_ZONE_PHASE.IDLE]:
        "Укажи улицу и дом — проверим зону и стоимость доставки.",
    [DELIVERY_ZONE_PHASE.PENDING]: CHECKOUT_LOADING_LABELS.zoneCheck,
    [DELIVERY_ZONE_PHASE.IN_ZONE]: "Адрес в зоне доставки",
    [DELIVERY_ZONE_PHASE.OUT_OF_ZONE]:
        "Адрес вне зоны — доплата за отдалённый район",
    [DELIVERY_ZONE_PHASE.UNKNOWN]:
        "Не удалось проверить адрес. Уточним доставку при подтверждении заказа.",
});

/**
 * @param {{
 *   method: string | null | undefined,
 *   addressReady: boolean,
 *   previewLoading: boolean,
 *   inZone: boolean | null | undefined,
 * }} input
 * @returns {string}
 */
export function resolveDeliveryZonePhase({
    method,
    addressReady,
    previewLoading,
    inZone,
}) {
    if (method === "pickup") {
        return DELIVERY_ZONE_PHASE.PICKUP;
    }

    if (method !== "courier") {
        return DELIVERY_ZONE_PHASE.HIDDEN;
    }

    if (!addressReady) {
        return DELIVERY_ZONE_PHASE.IDLE;
    }

    if (previewLoading) {
        return DELIVERY_ZONE_PHASE.PENDING;
    }

    if (inZone === true) {
        return DELIVERY_ZONE_PHASE.IN_ZONE;
    }

    if (inZone === false) {
        return DELIVERY_ZONE_PHASE.OUT_OF_ZONE;
    }

    return DELIVERY_ZONE_PHASE.UNKNOWN;
}

export function useDeliveryZoneStatus() {
    const checkoutStore = useCheckoutStore();
    const userStore = useUserStore();
    const { checkoutState } = useCheckoutFlowContext();
    const { deliveryInfo, flushing } = storeToRefs(checkoutStore);
    const { totals, previewLoading } = useOrderPreview();

    const addressReady = computed(() => {
        if (deliveryInfo.value.method !== "courier") {
            return false;
        }

        if (checkoutState.isGuestCheckout) {
            const draft = checkoutState.guestAddressDraft;
            const street = String(draft?.street ?? "").trim();
            const house = String(draft?.house ?? "").trim();

            return street !== "" && house !== "";
        }

        return userStore.selectedAddressId != null;
    });

    const phase = computed(() =>
        resolveDeliveryZonePhase({
            method: deliveryInfo.value.method,
            addressReady: addressReady.value,
            previewLoading: previewLoading.value || flushing.value,
            inZone: totals.value.inZone,
        }),
    );

    const message = computed(() => DELIVERY_ZONE_MESSAGES[phase.value] ?? null);

    const showPanel = computed(
        () =>
            deliveryInfo.value.method === "courier"
            && phase.value !== DELIVERY_ZONE_PHASE.HIDDEN,
    );

    return {
        phase,
        message,
        showPanel,
        addressReady,
        totals,
        previewLoading,
    };
}
