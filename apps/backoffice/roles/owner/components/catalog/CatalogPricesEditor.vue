<script setup>
import { computed } from "vue";
import UiFormField from "../../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../../shell/components/ui/UiInput.vue";

const props = defineProps({
    modelValue: { type: Array, default: () => [] },
    error: { type: String, default: "" },
});

const emit = defineEmits(["update:modelValue"]);

const rows = computed({
    get: () => props.modelValue ?? [],
    set: (value) => emit("update:modelValue", value),
});

const hasOld = computed(() => rows.value.some((row) => row.type === "old"));

function amountOf(type) {
    const row = rows.value.find((item) => item.type === type);
    return row?.amount ?? "";
}

function setAmount(type, value) {
    const index = rows.value.findIndex((item) => item.type === type);
    if (index === -1) {
        rows.value = [...rows.value, { type, amount: value }];
        return;
    }
    rows.value = rows.value.map((row, i) => (
        i === index ? { ...row, amount: value } : row
    ));
}

function addOld() {
    if (hasOld.value) {
        return;
    }
    rows.value = [...rows.value, { type: "old", amount: "" }];
}

function removeOld() {
    rows.value = rows.value.filter((row) => row.type !== "old");
}
</script>

<template>
    <div class="space-y-3">
        <p v-if="error" class="text-xs text-red-300">
            {{ error }}
        </p>

        <div class="grid gap-3 sm:grid-cols-2">
            <UiFormField label="Актуальная, ₽" required>
                <UiInput
                    :model-value="amountOf('regular')"
                    type="number"
                    @update:model-value="setAmount('regular', $event)"
                />
            </UiFormField>

            <UiFormField v-if="hasOld" label="Старая, ₽">
                <div class="space-y-1">
                    <UiInput
                        :model-value="amountOf('old')"
                        type="number"
                        @update:model-value="setAmount('old', $event)"
                    />
                    <button
                        type="button"
                        class="text-xs text-bo-muted transition-colors hover:text-bo-fg"
                        @click="removeOld"
                    >
                        Убрать
                    </button>
                </div>
            </UiFormField>
        </div>

        <button
            v-if="!hasOld"
            type="button"
            class="inline-flex h-9 items-center justify-center bg-white/10 px-4 text-sm font-medium text-bo-fg transition-colors duration-150 hover:bg-white/[0.14]"
            @click="addOld"
        >
            + Старая цена
        </button>
    </div>
</template>
