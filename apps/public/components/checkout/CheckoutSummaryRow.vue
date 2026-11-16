<script setup>
import { useAppDesign } from "../../design/useAppDesign";

defineProps({
    label: {
        type: String,
        required: true,
    },
    value: {
        type: String,
        required: true,
    },
    editLabel: {
        type: String,
        default: "Изменить",
    },
    showEdit: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(["edit"]);

const r = useAppDesign().components.checkout.summaryRow;
</script>

<template>
    <div :class="r.row">
        <div :class="r.body">
            <p :class="r.label">
                {{ label }}
            </p>
            <p :class="r.value">
                {{ value }}
            </p>
        </div>
        <button
            v-if="showEdit"
            type="button"
            :class="r.editLink"
            @click="emit('edit')"
        >
            {{ editLabel }}
        </button>
    </div>
</template>
