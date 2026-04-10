<script setup>
defineProps({
    cardsPerRow: {
        type: Number,
        default: 4,
    },
});

const emit = defineEmits(["update:cardsPerRow"]);
</script>

<template>
    <div class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-black/30 p-1">
        <span class="px-2 text-xs font-medium text-slate-400">Карточек:</span>
        <button
            type="button"
            class="rounded-xl px-3 py-1.5 text-xs font-semibold transition"
            :class="cardsPerRow === 3
                ? 'bg-amber-400 text-black'
                : 'text-slate-300 hover:bg-white/10 hover:text-slate-100'"
            @click="emit('update:cardsPerRow', 3)"
        >
            3
        </button>
        <button
            type="button"
            class="rounded-xl px-3 py-1.5 text-xs font-semibold transition"
            :class="cardsPerRow === 4
                ? 'bg-amber-400 text-black'
                : 'text-slate-300 hover:bg-white/10 hover:text-slate-100'"
            @click="emit('update:cardsPerRow', 4)"
        >
            4
        </button>
    </div>
</template>

<style scoped></style>
