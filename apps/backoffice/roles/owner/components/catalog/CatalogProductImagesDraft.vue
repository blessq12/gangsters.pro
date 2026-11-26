<script setup>
import { computed, onBeforeUnmount, ref } from "vue";
import draggable from "vuedraggable";
import UiButton from "../../../../shell/components/ui/UiButton.vue";

const props = defineProps({
    /** @type {{ localKey: string, id?: number, url: string, focus_x: number, focus_y: number, file?: File, isNew?: boolean }[]} */
    modelValue: { type: Array, default: () => [] },
    /** @type {number[]} */
    deletedIds: { type: Array, default: () => [] },
});

const emit = defineEmits(["update:modelValue", "update:deletedIds"]);

const fileInput = ref(null);
/** @type {import('vue').Ref<null | { localKey: string, rect: DOMRect }>} */
const focusDrag = ref(null);
let keySeq = 0;

const images = computed({
    get: () => props.modelValue ?? [],
    set: (value) => emit("update:modelValue", value),
});

function nextKey() {
    keySeq += 1;
    return `local-${Date.now()}-${keySeq}`;
}

function onFileChange(event) {
    const files = Array.from(event.target?.files ?? []);
    event.target.value = "";
    if (files.length === 0) {
        return;
    }
    const added = files.map((file) => {
        const url = URL.createObjectURL(file);
        return {
            localKey: nextKey(),
            url,
            focus_x: 50,
            focus_y: 50,
            file,
            isNew: true,
        };
    });
    images.value = [...images.value, ...added];
}

function setFocus(localKey, focusX, focusY) {
    const x = Math.min(100, Math.max(0, focusX));
    const y = Math.min(100, Math.max(0, focusY));
    images.value = images.value.map((row) =>
        row.localKey === localKey ? { ...row, focus_x: x, focus_y: y } : row,
    );
}

function focusFromEvent(localKey, rect, clientX, clientY) {
    if (!rect || rect.width <= 0 || rect.height <= 0) {
        return;
    }
    const focusX = ((clientX - rect.left) / rect.width) * 100;
    const focusY = ((clientY - rect.top) / rect.height) * 100;
    setFocus(localKey, focusX, focusY);
}

function startFocusDrag(event, image) {
    if (event.button != null && event.button !== 0) {
        return;
    }
    const target = event.currentTarget;
    if (!(target instanceof HTMLElement)) {
        return;
    }
    event.preventDefault();
    event.stopPropagation();
    const rect = target.getBoundingClientRect();
    focusDrag.value = { localKey: image.localKey, rect };
    focusFromEvent(image.localKey, rect, event.clientX, event.clientY);

    const onMove = (moveEvent) => {
        if (!focusDrag.value) {
            return;
        }
        focusFromEvent(
            focusDrag.value.localKey,
            focusDrag.value.rect,
            moveEvent.clientX,
            moveEvent.clientY,
        );
    };
    const onUp = () => {
        focusDrag.value = null;
        window.removeEventListener("pointermove", onMove);
        window.removeEventListener("pointerup", onUp);
    };
    window.addEventListener("pointermove", onMove);
    window.addEventListener("pointerup", onUp);
}

function removeImage(image) {
    if (image.url?.startsWith("blob:")) {
        URL.revokeObjectURL(image.url);
    }
    images.value = images.value.filter((row) => row.localKey !== image.localKey);
    if (image.id != null && !image.isNew) {
        emit("update:deletedIds", [...(props.deletedIds ?? []), Number(image.id)]);
    }
}

onBeforeUnmount(() => {
    for (const image of images.value) {
        if (image.url?.startsWith("blob:")) {
            URL.revokeObjectURL(image.url);
        }
    }
});
</script>

<template>
    <div class="space-y-4">
        <div class="flex flex-wrap items-center gap-3">
            <UiButton @click="fileInput?.click()">+ Фото</UiButton>
            <input
                ref="fileInput"
                type="file"
                accept="image/*"
                multiple
                class="hidden"
                @change="onFileChange"
            />
        </div>

        <div
            v-if="images.length === 0"
            class="border border-dashed border-bo-border px-4 py-8 text-sm text-bo-muted"
        >
            Пока без изображений
        </div>

        <draggable
            v-else
            v-model="images"
            item-key="localKey"
            handle=".image-reorder-handle"
            class="grid grid-cols-1 gap-4 lg:grid-cols-4"
        >
            <template #item="{ element }">
                <div class="space-y-3 border border-bo-border bg-bo-field/40 p-3">
                    <div class="flex items-center justify-between gap-2">
                        <button
                            type="button"
                            aria-label="Перетащить"
                            class="image-reorder-handle inline-flex h-9 w-9 cursor-grab items-center justify-center text-bo-muted hover:bg-white/[0.06] hover:text-bo-fg active:cursor-grabbing"
                        >
                            <span aria-hidden="true">⋮⋮</span>
                        </button>
                        <UiButton @click="removeImage(element)">Убрать</UiButton>
                    </div>

                    <div
                        class="relative aspect-square cursor-crosshair touch-none select-none overflow-hidden bg-black/30"
                        :style="{
                            backgroundImage: `url(${element.url})`,
                            backgroundSize: 'cover',
                            backgroundPosition: `${element.focus_x ?? 50}% ${element.focus_y ?? 50}%`,
                        }"
                        @pointerdown="startFocusDrag($event, element)"
                    >
                        <span
                            class="pointer-events-none absolute h-4 w-4 -translate-x-1/2 -translate-y-1/2 border-2 border-white bg-bo-accent/80 shadow-bo"
                            :style="{
                                left: `${element.focus_x ?? 50}%`,
                                top: `${element.focus_y ?? 50}%`,
                            }"
                        />
                    </div>
                </div>
            </template>
        </draggable>
    </div>
</template>
