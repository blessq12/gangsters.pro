<script setup>
import { computed, watch } from "vue";

const props = defineProps({
    open: { type: Boolean, default: false },
    title: { type: String, default: "" },
    /** md — узкое, lg — шире, xl — двухколоночные карточки */
    size: {
        type: String,
        default: "md",
        validator: (value) => ["md", "lg", "xl"].includes(value),
    },
});

const emit = defineEmits(["close"]);

const sizeClass = computed(() => {
    if (props.size === "xl") {
        return "max-w-6xl";
    }
    if (props.size === "lg") {
        return "max-w-4xl";
    }
    return "max-w-xl";
});

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
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-md sm:p-6"
            @click.self="emit('close')"
        >
            <div
                class="max-h-[90vh] w-full overflow-auto border border-bo-border-strong bg-bo-elevated text-bo-fg shadow-bo-lg"
                :class="sizeClass"
                role="dialog"
                aria-modal="true"
            >
                <header class="flex items-center justify-between px-6 pb-2 pt-6">
                    <h3 class="text-lg font-semibold tracking-tight text-bo-fg">
                        {{ title }}
                    </h3>
                    <button
                        type="button"
                        class="flex h-9 w-9 items-center justify-center text-xl text-bo-muted transition-colors hover:bg-white/[0.06] hover:text-bo-fg"
                        aria-label="Закрыть"
                        @click="emit('close')"
                    >
                        ×
                    </button>
                </header>
                <div class="px-6 py-5">
                    <slot />
                </div>
                <footer
                    v-if="$slots.footer"
                    class="flex justify-end gap-3 border-t border-bo-border px-6 py-4"
                >
                    <slot name="footer" />
                </footer>
            </div>
        </div>
    </Teleport>
</template>
