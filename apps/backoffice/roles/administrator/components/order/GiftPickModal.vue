<script setup>
import UiButton from "../../../../shell/components/ui/UiButton.vue";
import UiModal from "../../../../shell/components/ui/UiModal.vue";

defineProps({
    open: { type: Boolean, default: false },
    candidates: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["close", "pick", "decline"]);

/**
 * @param {Record<string, unknown>} candidate
 */
function pick(candidate) {
    const id = Number(candidate.product_id ?? candidate.id);
    if (id < 1) {
        return;
    }
    emit("pick", id);
}

function decline() {
    emit("decline");
}
</script>

<template>
    <UiModal
        :open="open"
        title="Выберите подарок"
        @close="emit('close')"
    >
        <p class="mb-4 text-sm text-bo-muted">
            Порог подарка достигнут. Выбери позицию или откажись от подарка.
        </p>
        <div
            v-if="candidates.length === 0"
            class="text-sm text-bo-muted"
        >
            Нет доступных кандидатов в меню филиала.
        </div>
        <div
            v-else
            class="grid gap-2"
        >
            <button
                v-for="item in candidates"
                :key="String(item.product_id ?? item.id)"
                type="button"
                class="border border-bo-border bg-bo-field px-4 py-3 text-left text-sm text-bo-fg transition-colors hover:border-bo-border-strong hover:bg-white/[0.04]"
                @click="pick(item)"
            >
                {{ item.name || `Товар #${item.product_id ?? item.id}` }}
            </button>
        </div>
        <div class="mt-5 flex flex-wrap justify-end gap-3">
            <UiButton
                class="!bg-white/10 !text-bo-fg"
                @click="decline"
            >
                Без подарка
            </UiButton>
        </div>
    </UiModal>
</template>
