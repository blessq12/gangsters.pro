<script setup>
import { ref } from "vue";
import { deleteItemImage, storeItemImage, updateItemImage } from "../../../../api/catalog.js";
import UiButton from "../../../../shell/components/ui/UiButton.vue";
import { useApi } from "../../../../shell/composables/useApi.js";
import { useToast } from "../../../../shell/composables/useToast.js";

const props = defineProps({
    itemId: { type: [Number, String], default: null },
    images: { type: Array, default: () => [] },
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(["update:images"]);

const toast = useToast();
const uploadApi = useApi();
const fileInput = ref(null);

function setImages(next) {
    emit("update:images", next);
}

async function onFileChange(event) {
    const file = event.target?.files?.[0];
    event.target.value = "";
    if (!file || props.disabled || props.itemId == null) {
        return;
    }
    try {
        const created = await uploadApi.run(() => storeItemImage(props.itemId, file, {
            focus_x: 50,
            focus_y: 50,
        }));
        setImages([...(props.images ?? []), created]);
        toast.success("Изображение загружено");
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить");
    }
}

async function saveFocus(image) {
    if (props.disabled || props.itemId == null) {
        return;
    }
    try {
        const updated = await updateItemImage(props.itemId, image.id, {
            focus_x: Number(image.focus_x ?? 50),
            focus_y: Number(image.focus_y ?? 50),
        });
        setImages((props.images ?? []).map((row) => (row.id === updated.id ? updated : row)));
        toast.success("Фокус сохранён");
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось сохранить фокус");
    }
}

async function removeImage(image) {
    if (props.disabled || props.itemId == null) {
        return;
    }
    try {
        await deleteItemImage(props.itemId, image.id);
        setImages((props.images ?? []).filter((row) => row.id !== image.id));
        toast.success("Удалено");
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось удалить");
    }
}

function patchLocal(imageId, key, value) {
    setImages((props.images ?? []).map((row) => (
        row.id === imageId ? { ...row, [key]: value } : row
    )));
}
</script>

<template>
    <div class="space-y-4">
        <div class="flex flex-wrap items-center gap-3">
            <UiButton
                :disabled="disabled || itemId == null || uploadApi.loading"
                @click="fileInput?.click()"
            >
                + Фото
            </UiButton>
            <input
                ref="fileInput"
                type="file"
                accept="image/*"
                class="hidden"
                @change="onFileChange"
            >
            <p v-if="disabled || itemId == null" class="text-sm text-bo-muted">
                Сначала сохраните карточку — потом можно грузить фото.
            </p>
        </div>

        <div
            v-if="(images ?? []).length === 0"
            class="border border-dashed border-bo-border px-4 py-8 text-sm text-bo-muted"
        >
            Пока без изображений
        </div>

        <ul class="grid gap-4 sm:grid-cols-2">
            <li
                v-for="image in images"
                :key="image.id"
                class="space-y-3 border border-bo-border bg-bo-field/40 p-3"
            >
                <div
                    class="aspect-square overflow-hidden bg-black/30"
                    :style="{
                        backgroundImage: `url(${image.url})`,
                        backgroundSize: 'cover',
                        backgroundPosition: `${image.focus_x ?? 50}% ${image.focus_y ?? 50}%`,
                    }"
                />
                <label class="block text-xs text-bo-muted">
                    Focus X
                    <input
                        type="number"
                        min="0"
                        max="100"
                        step="1"
                        class="mt-1 w-full border border-bo-border bg-bo-field px-3 py-2 text-sm"
                        :value="image.focus_x ?? 50"
                        :disabled="disabled"
                        @input="patchLocal(image.id, 'focus_x', Number($event.target.value))"
                    >
                </label>
                <label class="block text-xs text-bo-muted">
                    Focus Y
                    <input
                        type="number"
                        min="0"
                        max="100"
                        step="1"
                        class="mt-1 w-full border border-bo-border bg-bo-field px-3 py-2 text-sm"
                        :value="image.focus_y ?? 50"
                        :disabled="disabled"
                        @input="patchLocal(image.id, 'focus_y', Number($event.target.value))"
                    >
                </label>
                <div class="flex flex-wrap gap-2">
                    <UiButton :disabled="disabled" @click="saveFocus(image)">
                        Сохранить фокус
                    </UiButton>
                    <UiButton :disabled="disabled" @click="removeImage(image)">
                        Удалить
                    </UiButton>
                </div>
            </li>
        </ul>
    </div>
</template>
