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
    <div class="relative mb-10 w-full min-w-0 max-w-full">
        <div
            class="min-w-0 max-w-full rounded-2xl border border-amber-400/30 bg-[rgba(255,255,255,0.035)] px-4 lg:px-8 py-3.5 shadow-[0_0_22px_rgba(0,0,0,0.65)] backdrop-blur"
        >
            <div class="flex flex-wrap items-center gap-2 pb-1.5">
                <button
                    v-if="showAll"
                    type="button"
                    class="whitespace-nowrap rounded-full border px-5 py-2 text-sm transition-colors backdrop-blur bg-[rgba(0,0,0,0.75)]"
                    :class="
                        modelValue === null
                            ? 'border-amber-400/70 text-amber-100 shadow-[0_0_14px_rgba(251,191,36,0.45)]'
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
                    class="whitespace-nowrap rounded-full border px-5 py-2 text-sm transition-colors backdrop-blur bg-[rgba(0,0,0,0.75)]"
                    :class="
                        modelValue === (category.id ?? category.uri)
                            ? 'border-amber-400/70 text-amber-100 shadow-[0_0_14px_rgba(251,191,36,0.45)]'
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

