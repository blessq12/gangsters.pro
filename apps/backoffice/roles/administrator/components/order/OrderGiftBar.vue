<script setup>
import UiButton from "../../../../shell/components/ui/UiButton.vue";

defineProps({
    giftEligible: { type: Boolean, default: false },
    /** @type {null|number|'declined'} */
    giftDecision: { type: [Number, String, null], default: null },
    readonly: { type: Boolean, default: false },
});

defineEmits(["open"]);
</script>

<template>
    <div
        v-if="giftEligible || giftDecision !== null"
        class="flex flex-wrap items-center justify-between gap-2 border-t border-bo-border pt-3"
    >
        <p class="text-sm text-bo-muted">
            <template v-if="typeof giftDecision === 'number'">Подарок выбран</template>
            <template v-else-if="giftDecision === 'declined'">Без подарка</template>
            <template v-else>Доступен подарок</template>
        </p>
        <UiButton
            v-if="!readonly"
            class="!h-9 !bg-white/10 !px-3 !text-sm !text-bo-fg"
            @click="$emit('open')"
        >
            {{ giftDecision === null ? "Выбрать подарок" : "Изменить" }}
        </UiButton>
    </div>
</template>
