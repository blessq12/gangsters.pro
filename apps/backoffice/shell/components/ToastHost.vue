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
            class="pointer-events-auto border bg-bo-canvas px-3 py-2 text-sm shadow-bo"
            :class="
                item.variant === 'success'
                    ? 'border-emerald-500/50 text-emerald-300'
                    : 'border-bo-accent/60 text-red-300'
            "
        >
            {{ item.message }}
        </div>
    </div>
</template>
