<script setup>
defineProps({
    modelValue: { type: Boolean, default: false },
    labelOn: { type: String, default: "" },
    labelOff: { type: String, default: "" },
    label: { type: String, default: "" },
    disabled: { type: Boolean, default: false },
});

defineEmits(["update:modelValue"]);
</script>

<template>
    <button
        type="button"
        role="switch"
        :aria-checked="modelValue ? 'true' : 'false'"
        :disabled="disabled"
        class="inline-flex h-11 items-center justify-center gap-2.5 px-5 text-sm font-medium transition-colors duration-150 disabled:cursor-not-allowed disabled:opacity-40"
        :class="modelValue
            ? 'bg-white/10 text-bo-fg hover:bg-white/[0.14]'
            : 'bg-white/[0.04] text-bo-muted hover:bg-white/[0.08] hover:text-bo-fg'"
        @click="$emit('update:modelValue', !modelValue)"
    >
        <span
            class="relative h-4 w-7 shrink-0"
            :class="modelValue ? 'bg-bo-accent' : 'bg-white/20'"
        >
            <span
                class="absolute top-0.5 left-0.5 h-3 w-3 bg-white transition-transform duration-150"
                :class="modelValue ? 'translate-x-3' : 'translate-x-0'"
            />
        </span>
        <span>
            <template v-if="labelOn || labelOff">
                {{ modelValue ? (labelOn || label) : (labelOff || label) }}
            </template>
            <template v-else>
                {{ label }}
            </template>
        </span>
    </button>
</template>
