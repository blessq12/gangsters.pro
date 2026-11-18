<script setup>
defineProps({
    type: { type: String, default: "button" },
    disabled: { type: Boolean, default: false },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex h-10 items-center rounded bg-slate-900 px-4 text-sm text-white disabled:opacity-50"
        :disabled="disabled"
    >
        <slot />
    </button>
</template>
