<script setup>
defineProps({
    label: { type: String, default: "" },
    error: { type: String, default: "" },
    required: { type: Boolean, default: false },
});
</script>

<template>
    <label class="block text-sm text-slate-700">
        <span v-if="label" class="mb-1 block font-medium">
            {{ label }}
            <span v-if="required" class="text-rose-600">*</span>
        </span>
        <div
            class="rounded"
            :class="error ? 'ring-2 ring-rose-300' : ''"
        >
            <slot />
        </div>
        <span v-if="error" class="mt-1 block text-xs text-rose-600">
            {{ error }}
        </span>
    </label>
</template>
