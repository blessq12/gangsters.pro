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
            :class="n.backLink"
            @click="emit('back')"
        >
            {{ backLabel }}
        </button>
        <span
            v-else
            aria-hidden="true"
        />

        <div :class="n.primaryCluster">
            <span
                v-if="totalLabel"
                :class="n.totalLabel"
            >
                {{ totalLabel }}
            </span>
            <button
                type="button"
                :class="primaryClass"
                :disabled="primaryLoading || primaryDisabled"
                @click="emit('primary')"
            >
                {{ primaryText }}
            </button>
        </div>
    </div>
</template>
