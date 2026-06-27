<script setup>
import { computed } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import {
    DELIVERY_ZONE_PHASE,
    useDeliveryZoneStatus,
} from "../../features/checkout/useDeliveryZoneStatus";

const c = useAppDesign().components.checkout.cart;
const d = useAppDesign().components.checkout.delivery;

const { phase, message, showPanel } = useDeliveryZoneStatus();

const panelClass = computed(() => {
    switch (phase.value) {
        case DELIVERY_ZONE_PHASE.IDLE:
            return d.zoneStatusIdle;
        case DELIVERY_ZONE_PHASE.PENDING:
            return d.zoneStatusPending;
        case DELIVERY_ZONE_PHASE.IN_ZONE:
            return c.zoneStatusIn;
        case DELIVERY_ZONE_PHASE.OUT_OF_ZONE:
            return c.zoneStatusOut;
        case DELIVERY_ZONE_PHASE.UNKNOWN:
            return d.zoneStatusUnknown;
        default:
            return d.zoneStatusIdle;
    }
});
</script>

<template>
    <div
        v-if="showPanel && message"
        :class="[d.zonePanel, panelClass]"
        role="status"
        aria-live="polite"
    >
        {{ message }}
    </div>
</template>
