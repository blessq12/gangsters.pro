<script setup>
defineProps({
    title: { type: String, required: true },
    hint: { type: String, default: "" },
});
</script>

<template>
    <section class="mx-auto w-full max-w-3xl flex-1 px-4 py-8">
        <h1 class="text-xl font-semibold text-bo-fg">{{ title }}</h1>
        <p
            v-if="hint"
            class="mt-2 text-sm text-bo-muted"
        >
            {{ hint }}
        </p>
    </section>
</template>
