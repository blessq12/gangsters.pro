<script setup>
import BaseModal from "../ui/BaseModal.vue";
import { useAppDesign } from "../../design/useAppDesign";

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:modelValue", "merge", "replace", "cancel"]);

const shared = useAppDesign().components.client.shared;
const modal = useAppDesign().components.client.repeatOrderModal;

function close() {
    emit("update:modelValue", false);
    emit("cancel");
}

function chooseMerge() {
    emit("merge");
}

function chooseReplace() {
    emit("replace");
}
</script>

<template>
    <BaseModal
        :model-value="props.modelValue"
        @update:model-value="emit('update:modelValue', $event)"
    >
        <template #header>
            Корзина не пустая
        </template>

        <p :class="modal.lead">
            Добавить позиции из заказа к текущей корзине или заменить её полностью?
        </p>

        <template #footer>
            <div :class="modal.actions">
                <button
                    type="button"
                    :class="shared.btnPrimaryCompact"
                    :disabled="props.loading"
                    @click="chooseMerge"
                >
                    Добавить к корзине
                </button>
                <button
                    type="button"
                    :class="modal.replaceBtn"
                    :disabled="props.loading"
                    @click="chooseReplace"
                >
                    Заменить корзину
                </button>
                <button
                    type="button"
                    :class="modal.cancelBtn"
                    :disabled="props.loading"
                    @click="close"
                >
                    Отмена
                </button>
            </div>
        </template>
    </BaseModal>
</template>
