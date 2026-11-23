<script setup>
defineProps({
    title: { type: String, default: "" },
});
</script>

<template>
    <section class="border border-bo-border bg-bo-elevated shadow-bo">
        <header
            v-if="title || $slots.actions"
            class="flex flex-wrap items-center justify-between gap-4 px-6 pb-2 pt-6"
        >
            <h2 v-if="title" class="text-lg font-semibold tracking-tight text-bo-fg">
                {{ title }}
            </h2>
            <div v-if="$slots.actions" class="flex flex-wrap items-center gap-3">
                <slot name="actions" />
            </div>
        </header>
        <div class="p-6">
            <slot />
        </div>
    </section>
</template>
