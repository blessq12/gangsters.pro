<script setup>
import { watch } from "vue";

const props = defineProps({
    open: { type: Boolean, default: false },
    title: { type: String, default: "" },
});

const emit = defineEmits(["close"]);

watch(
    () => props.open,
    (isOpen) => {
        document.body.style.overflow = isOpen ? "hidden" : "";
    },
);
</script>

<template>
    <Teleport to="body">
        <div
            v-if="open"
            class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/40 p-4"
            @click.self="emit('close')"
        >
            <div
                class="max-h-[90vh] w-full max-w-lg overflow-auto rounded-lg bg-white shadow-xl"
                role="dialog"
                aria-modal="true"
            >
                <header class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                    <h3 class="text-base font-semibold text-slate-900">
                        {{ title }}
                    </h3>
                    <button
                        type="button"
                        class="rounded px-2 py-1 text-slate-500 hover:bg-slate-100"
                        aria-label="Закрыть"
                        @click="emit('close')"
                    >
                        ×
                    </button>
                </header>
                <div class="px-4 py-3">
                    <slot />
                </div>
                <footer
                    v-if="$slots.footer"
                    class="flex justify-end gap-2 border-t border-slate-100 px-4 py-3"
                >
                    <slot name="footer" />
                </footer>
            </div>
        </div>
    </Teleport>
</template>
