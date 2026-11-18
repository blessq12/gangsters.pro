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
        class="mt-1 w-full rounded border border-slate-300 bg-white px-3 py-2 text-slate-900"
        @input="$emit('update:modelValue', $event.target.value)"
    >
</template>
