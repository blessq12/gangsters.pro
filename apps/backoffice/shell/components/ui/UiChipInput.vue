<script setup>
import { computed, ref } from "vue";

const props = defineProps({
    modelValue: { type: Array, default: () => [] },
    placeholder: { type: String, default: "" },
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(["update:modelValue"]);

const draft = ref("");
const inputEl = ref(null);

const chips = computed({
    get: () => (Array.isArray(props.modelValue) ? props.modelValue : []),
    set: (value) => emit("update:modelValue", value),
});

function normalizeToken(raw) {
    return String(raw ?? "").trim().replace(/^,+|,+$/g, "").trim();
}

function commitDraft() {
    const token = normalizeToken(draft.value);
    draft.value = "";
    if (token === "") {
        return;
    }
    if (chips.value.some((chip) => chip.toLowerCase() === token.toLowerCase())) {
        return;
    }
    chips.value = [...chips.value, token];
}

function removeAt(index) {
    chips.value = chips.value.filter((_, i) => i !== index);
}

function onKeydown(event) {
    if (event.key === "Enter" || event.key === ",") {
        event.preventDefault();
        commitDraft();
        return;
    }
}

function onPaste(event) {
    const text = event.clipboardData?.getData("text") ?? "";
    if (!text.includes(",") && !text.includes("\n")) {
        return;
    }
    event.preventDefault();
    const parts = text.split(/[\n,;]+/);
    const next = [...chips.value];
    for (const part of parts) {
        const token = normalizeToken(part);
        if (token === "") {
            continue;
        }
        if (next.some((chip) => chip.toLowerCase() === token.toLowerCase())) {
            continue;
        }
        next.push(token);
    }
    chips.value = next;
    draft.value = "";
}

function onFieldClick(event) {
    // UiFormField оборачивает слот в <label>: клик иначе активирует первый ×.
    event.preventDefault();
    inputEl.value?.focus();
}

function onRemoveClick(event, index) {
    event.preventDefault();
    event.stopPropagation();
    removeAt(index);
}
</script>

<template>
    <div
        class="mt-2 flex min-h-11 w-full flex-wrap items-center gap-2 border border-bo-border bg-bo-field px-3 py-2 transition-colors hover:border-bo-border-strong focus-within:border-bo-accent"
        :class="disabled ? 'opacity-40' : 'cursor-text'"
        @click="onFieldClick"
    >
        <span
            v-for="(chip, index) in chips"
            :key="`${chip}-${index}`"
            class="inline-flex h-7 max-w-full items-center gap-1.5 bg-white/10 px-2.5 text-sm text-bo-fg"
        >
            <span class="truncate">{{ chip }}</span>
            <span
                role="button"
                tabindex="0"
                class="shrink-0 cursor-pointer text-bo-muted transition-colors hover:text-bo-fg"
                :aria-disabled="disabled ? 'true' : 'false'"
                :aria-label="`Убрать ${chip}`"
                @click="onRemoveClick($event, index)"
                @keydown.enter.prevent="onRemoveClick($event, index)"
            >
                ×
            </span>
        </span>
        <input
            ref="inputEl"
            v-model="draft"
            type="text"
            class="min-w-[8rem] flex-1 bg-transparent py-1 text-sm text-bo-fg outline-none placeholder:text-bo-muted/60"
            :placeholder="chips.length === 0 ? placeholder : ''"
            :disabled="disabled"
            @keydown="onKeydown"
            @blur="commitDraft"
            @paste="onPaste"
            @click.stop
        >
    </div>
</template>
