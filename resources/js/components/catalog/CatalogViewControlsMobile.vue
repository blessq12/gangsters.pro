<script setup>
defineProps({
    cardsPerRow: {
        type: Number,
        default: 1,
    },
    viewMode: {
        type: String,
        default: "grid", // grid | horizontal
    },
});

const emit = defineEmits(["update:cardsPerRow", "update:viewMode"]);
</script>

<template>
    <div class="space-y-2">
        <div class="inline-flex w-full items-center rounded-2xl border border-white/10 bg-black/30 p-1">
            <button
                type="button"
                class="flex-1 rounded-xl px-3 py-2 text-xs font-semibold transition"
                :class="viewMode === 'grid'
                    ? 'bg-amber-400 text-black'
                    : 'text-slate-300 hover:bg-white/10 hover:text-slate-100'"
                @click="emit('update:viewMode', 'grid')"
            >
                Сетка
            </button>
            <button
                type="button"
                class="flex-1 rounded-xl px-3 py-2 text-xs font-semibold transition"
                :class="viewMode === 'horizontal'
                    ? 'bg-amber-400 text-black'
                    : 'text-slate-300 hover:bg-white/10 hover:text-slate-100'"
                @click="emit('update:viewMode', 'horizontal')"
            >
                Горизонтально
            </button>
        </div>

        <div
            v-if="viewMode === 'grid'"
            class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-black/30 p-1"
        >
            <span class="px-2 text-xs font-medium text-slate-400">В ряд:</span>
            <button
                type="button"
                class="rounded-xl px-3 py-1.5 text-xs font-semibold transition"
                :class="cardsPerRow === 1
                    ? 'bg-amber-400 text-black'
                    : 'text-slate-300 hover:bg-white/10 hover:text-slate-100'"
                @click="emit('update:cardsPerRow', 1)"
            >
                1
            </button>
            <button
                type="button"
                class="rounded-xl px-3 py-1.5 text-xs font-semibold transition"
                :class="cardsPerRow === 2
                    ? 'bg-amber-400 text-black'
                    : 'text-slate-300 hover:bg-white/10 hover:text-slate-100'"
                @click="emit('update:cardsPerRow', 2)"
            >
                2
            </button>
        </div>
    </div>
</template>

<style scoped></style>
