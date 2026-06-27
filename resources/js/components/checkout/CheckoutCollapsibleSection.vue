<script setup>
import { ref } from "vue";
import { useAppDesign } from "../../design/useAppDesign";

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    defaultOpen: {
        type: Boolean,
        default: false,
    },
});

const open = ref(props.defaultOpen);
const s = useAppDesign().components.checkout.shared;
</script>

<template>
    <div :class="s.borderSectionTop">
        <button
            type="button"
            :class="s.expandRowBtn"
            @click="open = !open"
        >
            <span>{{ title }}</span>
            <span :class="s.expandRowChevronMuted">
                {{ open ? "Скрыть" : "Добавить" }}
            </span>
        </button>

        <Transition name="checkout-fade">
            <div
                v-if="open"
                :class="s.newAddressWrap"
            >
                <slot />
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.checkout-fade-enter-active,
.checkout-fade-leave-active {
    transition: opacity 0.2s ease;
}
.checkout-fade-enter-from,
.checkout-fade-leave-to {
    opacity: 0;
}
</style>
