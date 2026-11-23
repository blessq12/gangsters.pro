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
    success: "bg-emerald-500/12 text-emerald-300",
    warning: "bg-amber-500/12 text-amber-300",
    danger: "bg-bo-accent-soft text-red-300",
    neutral: "bg-white/[0.06] text-bo-muted",
};
</script>

<template>
    <span
        class="inline-flex items-center px-2.5 py-1 text-xs font-medium"
        :class="classes[variant] || classes.neutral"
    >
        {{ text }}
    </span>
</template>
