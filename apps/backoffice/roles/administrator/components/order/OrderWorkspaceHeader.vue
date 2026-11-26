<script setup>
import UiBadge from "../../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../../shell/components/ui/UiButton.vue";

defineProps({
    title: { type: String, required: true },
    statusLabel: { type: String, default: "" },
    statusVariant: { type: String, default: "neutral" },
    modeLabel: { type: String, default: "" },
    backLabel: { type: String, default: "Назад" },
    primaryLabel: { type: String, default: "" },
    primaryDisabled: { type: Boolean, default: false },
    secondaryLabel: { type: String, default: "" },
    commands: { type: Array, default: () => [] },
    commandsDisabled: { type: Boolean, default: false },
});

defineEmits(["back", "primary", "secondary", "command"]);
</script>

<template>
    <div class="space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
                <UiButton class="!bg-white/10 !text-bo-fg" @click="$emit('back')">
                    {{ backLabel }}
                </UiButton>
                <h1 class="text-lg font-semibold tracking-tight text-bo-fg">
                    {{ title }}
                </h1>
                <UiBadge v-if="statusLabel" :variant="statusVariant" :text="statusLabel" />
                <UiBadge v-if="modeLabel" variant="neutral" :text="modeLabel" />
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <slot name="actions" />
                <UiButton
                    v-for="command in commands"
                    :key="command.id"
                    :disabled="commandsDisabled"
                    @click="$emit('command', command.id)"
                >
                    {{ command.label }}
                </UiButton>
                <UiButton
                    v-if="secondaryLabel"
                    class="!bg-white/10 !text-bo-fg"
                    @click="$emit('secondary')"
                >
                    {{ secondaryLabel }}
                </UiButton>
                <UiButton v-if="primaryLabel" :disabled="primaryDisabled" @click="$emit('primary')">
                    {{ primaryLabel }}
                </UiButton>
            </div>
        </div>
    </div>
</template>
