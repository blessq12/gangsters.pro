<script setup>
defineProps({
    title: { type: String, required: true },
    hint: { type: String, default: "" },
});
</script>

<template>
    <section class="mx-auto w-full max-w-3xl flex-1 px-4 py-8">
        <h1 class="text-xl font-medium">{{ title }}</h1>
        <p
            v-if="hint"
            class="mt-2 text-sm text-slate-500"
        >
            {{ hint }}
        </p>
    </section>
</template>
