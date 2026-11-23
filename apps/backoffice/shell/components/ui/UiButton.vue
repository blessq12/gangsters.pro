<script setup>
defineProps({
    type: { type: String, default: "button" },
    disabled: { type: Boolean, default: false },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex h-11 items-center justify-center gap-2 bg-bo-accent px-5 text-sm font-medium text-white transition-colors duration-150 hover:bg-bo-accent-hover disabled:cursor-not-allowed disabled:opacity-40"
        :disabled="disabled"
    >
        <slot />
    </button>
</template>
