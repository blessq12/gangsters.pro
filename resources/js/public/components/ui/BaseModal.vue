<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from "vue";
import { playModalClose, playModalOpen } from "../../animations/animationManager";
import {
    pushBodyScrollLock,
    popBodyScrollLock,
} from "../../platform/document";
import { useAppDesign } from "../../design/useAppDesign";

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    closable: {
        type: Boolean,
        default: true,
    },
    /** md — компактное окно; lg — шире + скролл тела */
    size: {
        type: String,
        default: "md",
        validator: (value) => value === "md" || value === "lg",
    },
});

const emit = defineEmits(["update:modelValue"]);

const dm = useAppDesign().components.uiPrimitives.modal;

const sizeClasses = computed(() => {
    const sizes = dm.sizes;
    return sizes[props.size] || sizes.md;
});

const isVisible = ref(false);
const backdropRef = ref(null);
const cardRef = ref(null);
let bodyScrollLocksHeld = 0;

const lockBodyScroll = () => {
    pushBodyScrollLock();
    bodyScrollLocksHeld += 1;
};

const unlockBodyScroll = () => {
    if (bodyScrollLocksHeld === 0) return;
    popBodyScrollLock();
    bodyScrollLocksHeld -= 1;
};

const close = () => {
    if (!props.closable) {
        return;
    }

    emit("update:modelValue", false);
};

watch(
    () => props.modelValue,
    async (val) => {
        if (val) {
            isVisible.value = true;
            lockBodyScroll();
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
                    unlockBodyScroll();
                },
            });
        }
    },
    { immediate: true },
);

onBeforeUnmount(() => {
    while (bodyScrollLocksHeld > 0) {
        popBodyScrollLock();
        bodyScrollLocksHeld -= 1;
    }
});
</script>

<template>
    <teleport to="body">
        <div v-if="isVisible" :class="dm.root">
            <div
                ref="backdropRef"
                :class="dm.backdrop"
                @click="props.closable ? close() : undefined"
            />
            <div :class="dm.content">
                <div :class="sizeClasses.innerWrap">
                    <div ref="cardRef" :class="sizeClasses.card">
                        <div
                            v-if="$slots.header || props.closable"
                            :class="dm.headerRow"
                        >
                            <div :class="dm.headerSlot">
                                <slot name="header" />
                            </div>
                            <button
                                v-if="props.closable"
                                type="button"
                                :class="dm.closeBtn"
                                @click="close"
                            >
                                ✕
                            </button>
                        </div>

                        <div :class="sizeClasses.body">
                            <slot />
                        </div>

                        <div
                            v-if="$slots.footer"
                            :class="dm.footerWrap"
                        >
                            <slot name="footer" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </teleport>
</template>
