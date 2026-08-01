<script setup>
import { computed } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { CHECKOUT_NAV_LABELS } from "../../features/checkout/checkoutWizardLabels";

const props = defineProps({
    showBack: {
        type: Boolean,
        default: true,
    },
    backLabel: {
        type: String,
        default: CHECKOUT_NAV_LABELS.back,
    },
    primaryLabel: {
        type: String,
        required: true,
    },
    primaryBusyLabel: {
        type: String,
        default: "",
    },
    primaryLoading: {
        type: Boolean,
        default: false,
    },
    primaryDisabled: {
        type: Boolean,
        default: false,
    },
    totalLabel: {
        type: String,
        default: "",
    },
    showNavTotal: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["back", "primary"]);

const n = useAppDesign().components.checkout.nav;

const primaryClass = computed(() =>
    props.primaryLoading || props.primaryDisabled
        ? n.btnPrimaryBusy
        : n.btnPrimary,
);

const primaryText = computed(() =>
    props.primaryLoading && props.primaryBusyLabel
        ? props.primaryBusyLabel
        : props.primaryLabel,
);
</script>

<template>
    <div :class="n.row">
        <button
            v-if="showBack"
            type="button"
            :class="n.backBtn"
            :aria-label="backLabel"
            :title="backLabel"
            @click="emit('back')"
        >
            <i :class="n.backIcon" aria-hidden="true" />
        </button>

        <button
            type="button"
            :class="primaryClass"
            :disabled="primaryLoading || primaryDisabled"
            @click="emit('primary')"
        >
            <span :class="n.sheen" aria-hidden="true" />
            <span :class="n.primaryContent">
                <span :class="n.primaryLabel">{{ primaryText }}</span>
                <span
                    v-if="showNavTotal && totalLabel"
                    :class="n.totalLabel"
                >
                    {{ totalLabel }}
                </span>
            </span>
        </button>
    </div>
</template>

<style scoped>
.checkout-wizard-cta-sheen::before {
    content: "";
    position: absolute;
    inset: 0;
    width: 45%;
    background: linear-gradient(
        105deg,
        transparent 0%,
        rgba(255, 255, 255, 0.12) 35%,
        rgba(255, 255, 255, 0.38) 50%,
        rgba(255, 255, 255, 0.12) 65%,
        transparent 100%
    );
    transform: translateX(-140%) skewX(-18deg);
    animation: checkout-wizard-cta-sheen-move 2.8s ease-in-out infinite;
}

@keyframes checkout-wizard-cta-sheen-move {
    0% {
        transform: translateX(-140%) skewX(-18deg);
    }
    55%,
    100% {
        transform: translateX(260%) skewX(-18deg);
    }
}

@media (prefers-reduced-motion: reduce) {
    .checkout-wizard-cta-sheen::before {
        animation: none;
        display: none;
    }
}
</style>
