<script setup>
defineProps({
    modelValue: { type: String, default: "" },
    type: { type: String, default: "text" },
    required: { type: Boolean, default: false },
    autocomplete: { type: String, default: "off" },
});
defineEmits(["update:modelValue"]);
</script>

<template>
    <input
        :type="type"
        :value="modelValue"
        :required="required"
        :autocomplete="autocomplete"
        class="mt-2 h-11 w-full border border-bo-border bg-bo-field px-4 text-bo-fg transition-colors placeholder:text-bo-muted/60 hover:border-bo-border-strong focus:border-bo-accent focus:outline-none"
        @input="$emit('update:modelValue', $event.target.value)"
    >
</template>
