<script setup>
import { computed } from "vue";
import BaseModal from "../../ui/BaseModal.vue";
import { useAppDesign } from "../../../design/useAppDesign";

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: "",
    },
    message: {
        type: String,
        default: "",
    },
    confirmLabel: {
        type: String,
        default: "Продолжить",
    },
    cancelLabel: {
        type: String,
        default: "Отмена",
    },
});

const emit = defineEmits(["update:modelValue", "confirm", "cancel"]);

const d = useAppDesign().components.dockDismissConfirm;

const isOpen = computed({
    get() {
        return props.modelValue;
    },
    set(next) {
        emit("update:modelValue", next);
    },
});

function onStay() {
    emit("cancel");
    emit("update:modelValue", false);
}

function onLeave() {
    emit("confirm");
    emit("update:modelValue", false);
}
</script>

<template>
    <BaseModal
        v-model="isOpen"
        :closable="false"
    >
        <div :class="d.contentWrap">
            <h2 :class="d.title">
                {{ title }}
            </h2>
            <p :class="d.message">
                {{ message }}
            </p>

            <div :class="d.actions">
                <button
                    type="button"
                    :class="d.primaryBtn"
                    @click="onStay"
                >
                    {{ cancelLabel }}
                </button>
                <button
                    type="button"
                    :class="d.secondaryBtn"
                    @click="onLeave"
                >
                    {{ confirmLabel }}
                </button>
            </div>
        </div>
    </BaseModal>
</template>
