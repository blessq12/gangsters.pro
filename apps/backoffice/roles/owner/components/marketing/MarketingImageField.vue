<script setup>
import { computed, onBeforeUnmount, ref, watch } from "vue";
import UiButton from "../../../../shell/components/ui/UiButton.vue";

const props = defineProps({
    label: { type: String, default: "Изображение" },
    /** Текущий URL с сервера */
    currentUrl: { type: String, default: null },
    /** Выбранный локальный файл */
    modelValue: { type: File, default: null },
    required: { type: Boolean, default: false },
    error: { type: String, default: "" },
    disabled: { type: Boolean, default: false },
    /** Разрешить сброс картинки (для акции) */
    clearable: { type: Boolean, default: false },
    /** Пользователь снял существующую картинку */
    markedForRemoval: { type: Boolean, default: false },
});

const emit = defineEmits(["update:modelValue", "update:markedForRemoval"]);

const fileInput = ref(null);
const previewUrl = ref(null);

const displayUrl = computed(() => {
    if (previewUrl.value) {
        return previewUrl.value;
    }
    if (props.markedForRemoval) {
        return null;
    }
    return props.currentUrl || null;
});

watch(
    () => props.modelValue,
    (file) => {
        if (previewUrl.value?.startsWith("blob:")) {
            URL.revokeObjectURL(previewUrl.value);
        }
        previewUrl.value = file instanceof File ? URL.createObjectURL(file) : null;
    },
    { immediate: true },
);

onBeforeUnmount(() => {
    if (previewUrl.value?.startsWith("blob:")) {
        URL.revokeObjectURL(previewUrl.value);
    }
});

function pick() {
    if (props.disabled) {
        return;
    }
    fileInput.value?.click();
}

function onFileChange(event) {
    const file = event.target?.files?.[0] ?? null;
    event.target.value = "";
    if (!file) {
        return;
    }
    emit("update:markedForRemoval", false);
    emit("update:modelValue", file);
}

function clear() {
    emit("update:modelValue", null);
    if (props.currentUrl) {
        emit("update:markedForRemoval", true);
    }
}
</script>

<template>
    <div class="space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm font-medium text-bo-fg">
                {{ label }}
                <span v-if="required" class="text-bo-accent">*</span>
            </p>
            <div class="flex flex-wrap gap-2">
                <UiButton :disabled="disabled" @click="pick">
                    {{ displayUrl ? "Заменить" : "Выбрать файл" }}
                </UiButton>
                <UiButton v-if="clearable && displayUrl" :disabled="disabled" @click="clear">
                    Убрать
                </UiButton>
            </div>
            <input
                ref="fileInput"
                type="file"
                accept="image/*"
                class="hidden"
                @change="onFileChange"
            />
        </div>

        <div v-if="displayUrl" class="overflow-hidden border border-bo-border bg-black/20">
            <img :src="displayUrl" alt="" class="max-h-64 w-full object-contain" />
        </div>
        <div v-else class="border border-dashed border-bo-border px-4 py-8 text-sm text-bo-muted">
            Файл не выбран
        </div>
        <p v-if="error" class="text-sm text-red-400">
            {{ error }}
        </p>
    </div>
</template>
