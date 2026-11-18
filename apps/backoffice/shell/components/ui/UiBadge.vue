<script setup>
defineProps({
    variant: {
        type: String,
        default: "neutral",
        validator: (v) => ["success", "warning", "danger", "neutral"].includes(v),
    },
    text: { type: String, required: true },
});

const classes = {
    success: "bg-emerald-100 text-emerald-800",
    warning: "bg-amber-100 text-amber-800",
    danger: "bg-rose-100 text-rose-800",
    neutral: "bg-slate-100 text-slate-700",
};
</script>

<template>
    <span
        class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
        :class="classes[variant] || classes.neutral"
    >
        {{ text }}
    </span>
</template>
