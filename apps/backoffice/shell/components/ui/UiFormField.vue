<script setup>
defineProps({
    label: { type: String, default: "" },
    error: { type: String, default: "" },
    required: { type: Boolean, default: false },
});
</script>

<template>
    <div class="block text-sm text-bo-fg">
        <div
            v-if="label"
            class="mb-2 block text-xs font-medium uppercase tracking-[0.1em] text-bo-muted"
        >
            {{ label }}
            <span v-if="required" class="text-bo-accent">*</span>
        </div>
        <div :class="error ? 'ring-1 ring-bo-accent' : ''">
            <slot />
        </div>
        <span v-if="error" class="mt-2 block text-xs text-red-300">
            {{ error }}
        </span>
    </div>
</template>
