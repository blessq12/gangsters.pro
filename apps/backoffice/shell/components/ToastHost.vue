<script setup>
import { useToast } from "../composables/useToast.js";

const toast = useToast();
</script>

<template>
    <div
        class="pointer-events-none fixed right-4 top-4 z-50 flex w-80 max-w-[calc(100vw-2rem)] flex-col gap-2"
        aria-live="polite"
    >
        <div
            v-for="item in toast.items"
            :key="item.id"
            class="pointer-events-auto rounded border px-3 py-2 text-sm shadow"
            :class="item.variant === 'success'
                ? 'border-emerald-200 bg-emerald-50 text-emerald-900'
                : 'border-rose-200 bg-rose-50 text-rose-900'"
        >
            {{ item.message }}
        </div>
    </div>
</template>
