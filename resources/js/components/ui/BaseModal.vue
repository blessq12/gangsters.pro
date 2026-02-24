<script setup>
import { nextTick, ref, watch } from "vue";
import { playModalClose, playModalOpen } from "../../animations/animationManager";

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    closable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(["update:modelValue"]);

const isVisible = ref(false);
const backdropRef = ref(null);
const cardRef = ref(null);

const close = () => {
    emit("update:modelValue", false);
};

watch(
    () => props.modelValue,
    async (val) => {
        if (val) {
            isVisible.value = true;
            await nextTick();
            playModalOpen({
                backdrop: backdropRef.value,
                card: cardRef.value,
            });
        } else if (isVisible.value) {
            playModalClose({
                backdrop: backdropRef.value,
                card: cardRef.value,
                onComplete: () => {
                    isVisible.value = false;
                },
            });
        }
    },
);
</script>

<template>
    <teleport to="body">
        <div v-if="isVisible" class="base-modal">
            <div ref="backdropRef" class="backdrop" @click="close"></div>
            <div class="content">
                <div
                    class="mx-auto w-full max-w-lg px-4 sm:px-6 lg:px-8 relative z-10"
                >
                    <div
                        ref="cardRef"
                        class="rounded-2xl border border-white/10 bg-[rgba(255,255,255,0.04)] px-4 sm:px-6 lg:px-8 py-5 shadow-2xl shadow-black/60 backdrop-blur-lg"
                    >
                        <div
                            v-if="$slots.header || props.closable"
                            class="flex items-start justify-between gap-4 mb-4"
                        >
                            <div class="text-base font-semibold text-amber-300">
                                <slot name="header" />
                            </div>
                            <button
                                v-if="props.closable"
                                type="button"
                                class="text-slate-400 hover:text-white transition-colors"
                                @click="close"
                            >
                                ✕
                            </button>
                        </div>

                        <div class="space-y-4 text-sm leading-relaxed text-slate-100">
                            <slot />
                        </div>

                        <div
                            v-if="$slots.footer"
                            class="mt-4 pt-3 border-t border-white/10"
                        >
                            <slot name="footer" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </teleport>
</template>

<style scoped>
.base-modal {
    position: fixed;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.backdrop {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
}

.content {
    position: relative;
    z-index: 1;
}
</style>

