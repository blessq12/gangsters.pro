<script setup>
const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
    modelValue: {
        type: [Number, String, null],
        default: null,
    },
    allLabel: {
        type: String,
        default: "Все",
    },
    showAll: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(["update:modelValue", "change"]);

const handleSelect = (value) => {
    emit("update:modelValue", value);
    emit("change", value);
};
</script>

<template>
    <div class="relative mb-10">
        <div
            class="rounded-2xl border border-amber-400/30 bg-[rgba(255,255,255,0.035)] px-4 sm:px-6 lg:px-8 py-3.5 shadow-[0_0_22px_rgba(0,0,0,0.65)] backdrop-blur"
        >
            <div
                class="flex items-center gap-3 overflow-x-auto lg:overflow-visible pb-1.5 scrollbar-thin scrollbar-thumb-slate-700 scrollbar-track-transparent"
            >
                <button
                    v-if="showAll"
                    type="button"
                    class="whitespace-nowrap rounded-full border px-5 py-2 text-xs sm:text-sm md:text-[0.9rem] transition-colors backdrop-blur bg-[rgba(0,0,0,0.75)]"
                    :class="
                        modelValue === null
                            ? 'border-amber-400/70 text-amber-100 shadow-[0_0_20px_rgba(251,191,36,0.7)]'
                            : 'border-white/10 text-slate-300 hover:border-amber-400/50 hover:text-amber-200'
                    "
                    @click="handleSelect(null)"
                >
                    {{ allLabel }}
                </button>

                <button
                    v-for="category in categories"
                    :key="category.id ?? category.uri"
                    type="button"
                    class="whitespace-nowrap rounded-full border px-5 py-2 text-xs sm:text-sm md:text-[0.9rem] transition-colors backdrop-blur bg-[rgba(0,0,0,0.75)]"
                    :class="
                        modelValue === (category.id ?? category.uri)
                            ? 'border-amber-400/70 text-amber-100 shadow-[0_0_20px_rgba(251,191,36,0.7)]'
                            : 'border-white/10 text-slate-300 hover:border-amber-400/50 hover:text-amber-200'
                    "
                    @click="handleSelect(category.id ?? category.uri)"
                >
                    {{ category.name }}
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
