<script setup>
defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
    to: { type: [Object, String], default: null },
});
</script>

<template>
    <component
        :is="to ? 'RouterLink' : 'div'"
        :to="to || undefined"
        class="block border border-bo-border bg-bo-elevated px-4 py-4 transition-colors"
        :class="to ? 'hover:border-bo-accent' : ''"
    >
        <p class="text-xs uppercase tracking-[0.12em] text-bo-muted">
            {{ label }}
        </p>
        <p class="mt-2 text-2xl font-semibold tabular-nums text-bo-fg">
            {{ value }}
        </p>
    </component>
</template>
