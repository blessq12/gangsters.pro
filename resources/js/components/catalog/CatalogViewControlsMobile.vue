<script setup>
defineProps({
    viewMode: {
        type: String,
        default: "grid", // grid | horizontal
    },
});

const emit = defineEmits(["update:viewMode"]);
</script>

<template>
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
</template>

<style scoped></style>
