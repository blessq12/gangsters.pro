<script setup>
import { computed } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import {
    DELIVERY_UNAVAILABLE_COPY,
    DELIVERY_ZONE_PHASE,
    useDeliveryZoneStatus,
} from "../../modules/checkout/application/delivery";
import { useCheckoutFlowContext } from "../../modules/checkout/application/flowContext";

const c = useAppDesign().components.checkout.cart;
const d = useAppDesign().components.checkout.delivery;

const { phase, message, showPanel } = useDeliveryZoneStatus();
const { setDeliveryMethod } = useCheckoutFlowContext();

const isUnavailable = computed(
    () => phase.value === DELIVERY_ZONE_PHASE.UNAVAILABLE,
);

const panelClass = computed(() => {
    switch (phase.value) {
        case DELIVERY_ZONE_PHASE.IDLE:
            return d.zoneStatusIdle;
        case DELIVERY_ZONE_PHASE.PENDING:
            return d.zoneStatusPending;
        case DELIVERY_ZONE_PHASE.FREE:
            return d.zoneStatusFree ?? c.zoneStatusIn;
        case DELIVERY_ZONE_PHASE.REMOTE:
            return d.zoneStatusRemote ?? c.zoneStatusOut;
        case DELIVERY_ZONE_PHASE.PAID:
            return d.zoneStatusPaid ?? c.zoneStatusIn;
        case DELIVERY_ZONE_PHASE.UNAVAILABLE:
            return d.zoneStatusUnavailable ?? c.zoneStatusOut;
        case DELIVERY_ZONE_PHASE.UNKNOWN:
            return d.zoneStatusUnknown;
        default: {
            const _exhaustive = phase.value;
            void _exhaustive;
            return d.zoneStatusIdle;
        }
    }
});

function switchToPickup() {
    setDeliveryMethod("pickup");
}
</script>

<template>
    <div
        v-if="showPanel && message"
        :class="[d.zonePanel, panelClass]"
        role="status"
        aria-live="polite"
    >
        <template v-if="isUnavailable">
            <p :class="d.zoneUnavailableTitle">
                {{ DELIVERY_UNAVAILABLE_COPY.title }}
            </p>
            <p :class="d.zoneUnavailableBody">
                {{ DELIVERY_UNAVAILABLE_COPY.body }}
            </p>
            <button
                type="button"
                :class="d.zoneUnavailableCta"
                @click="switchToPickup"
            >
                {{ DELIVERY_UNAVAILABLE_COPY.pickupCta }}
            </button>
        </template>
        <template v-else>
            {{ message }}
        </template>
    </div>
</template>
