<script setup>
defineProps({
    title: { type: String, default: "" },
});
</script>

<template>
    <section class="rounded border border-slate-200 bg-white shadow-sm">
        <header
            v-if="title || $slots.actions"
            class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-3"
        >
            <h2 v-if="title" class="text-base font-semibold text-slate-900">
                {{ title }}
            </h2>
            <div v-if="$slots.actions" class="flex flex-wrap items-center gap-2">
                <slot name="actions" />
            </div>
        </header>
        <div class="p-4">
            <slot />
        </div>
    </section>
</template>
