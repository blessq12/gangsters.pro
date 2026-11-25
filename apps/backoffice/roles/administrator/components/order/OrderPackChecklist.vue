<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import ShellIcon from "../../../../shell/components/ShellIcon.vue";
import UiButton from "../../../../shell/components/ui/UiButton.vue";

const props = defineProps({
    lines: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    actionLoading: { type: Boolean, default: false },
    /** Сборка доступна (заказ ready) — кнопка пульсирует */
    active: { type: Boolean, default: false },
});

defineEmits(["pack"]);

const open = ref(false);
const rootEl = ref(/** @type {HTMLElement|null} */ (null));

/**
 * @param {Record<string, unknown>} line
 */
function canPack(line) {
    return Boolean(line?.id) && line.status === "done" && !line.packed_by;
}

const pendingCount = computed(() =>
    props.lines.filter((line) => canPack(line)).length,
);

const packedCount = computed(() =>
    props.lines.filter((line) => Boolean(line?.packed_by)).length,
);

const attention = computed(() => props.active && pendingCount.value > 0);

function toggle() {
    open.value = !open.value;
}

function close() {
    open.value = false;
}

/**
 * @param {MouseEvent} event
 */
function onDocumentClick(event) {
    if (!open.value || !rootEl.value) {
        return;
    }
    const target = event.target;
    if (target instanceof Node && !rootEl.value.contains(target)) {
        close();
    }
}

onMounted(() => {
    document.addEventListener("click", onDocumentClick);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", onDocumentClick);
});
</script>

<template>
    <div
        ref="rootEl"
        class="relative"
    >
        <button
            type="button"
            class="pack-trigger relative inline-flex h-11 items-center gap-2 border px-3 text-bo-fg"
            :class="attention
                ? 'pack-trigger--attention border-bo-accent bg-bo-accent/25'
                : 'border-bo-border bg-white/10 hover:bg-white/[0.08]'"
            :aria-expanded="open"
            aria-label="Сборка позиций"
            @click.stop="toggle"
        >
            <ShellIcon
                name="pack"
                class="h-5 w-5"
            />
            <span
                class="inline-flex min-w-[1.25rem] items-center justify-center rounded-sm px-1.5 text-xs font-semibold"
                :class="pendingCount > 0
                    ? 'bg-bo-accent/30 text-bo-fg'
                    : 'bg-white/10 text-bo-muted'"
            >
                {{ pendingCount }}
            </span>
        </button>

        <div
            v-if="open"
            class="absolute right-0 z-40 mt-2 w-[22rem] max-w-[calc(100vw-2rem)] border border-bo-border bg-bo-elevated p-3 shadow-bo-lg sm:w-[26rem]"
            @click.stop
        >
            <div class="mb-3 flex items-center justify-between gap-2">
                <div>
                    <p class="text-sm font-medium text-bo-fg">
                        Сборка позиций
                    </p>
                    <p class="text-xs text-bo-muted">
                        На месте: {{ packedCount }} / {{ lines.length }}
                    </p>
                </div>
                <button
                    type="button"
                    class="text-bo-muted hover:text-bo-fg"
                    aria-label="Закрыть"
                    @click="close"
                >
                    ×
                </button>
            </div>

            <p class="mb-3 text-sm text-bo-muted">
                Подтверди, что каждая позиция на месте. После всех отметок заказ станет «собран».
            </p>

            <p
                v-if="loading"
                class="text-sm text-bo-muted"
            >
                Загрузка тикета…
            </p>
            <ul
                v-else-if="lines.length"
                class="max-h-72 space-y-2 overflow-y-auto text-sm"
            >
                <li
                    v-for="line in lines"
                    :key="line.id ?? `${line.product_id}-${line.name}`"
                    class="flex flex-wrap items-center justify-between gap-3 border border-bo-border bg-black/20 px-3 py-2"
                >
                    <span>
                        {{ line.name || `Позиция #${line.product_id}` }}
                        × {{ line.quantity }}
                        <span
                            v-if="line.packed_by"
                            class="ml-2 text-emerald-300"
                        >собрано</span>
                        <span
                            v-else-if="line.status !== 'done'"
                            class="ml-2 text-bo-muted"
                        >ещё на кухне</span>
                    </span>
                    <UiButton
                        v-if="canPack(line)"
                        :disabled="actionLoading"
                        @click="$emit('pack', line)"
                    >
                        На месте
                    </UiButton>
                </li>
            </ul>
            <p
                v-else
                class="text-sm text-bo-muted"
            >
                Тикет не найден.
            </p>
        </div>
    </div>
</template>

<style scoped>
.pack-trigger--attention {
    animation: pack-attention 1.4s ease-in-out infinite;
}

@keyframes pack-attention {
    0%,
    100% {
        box-shadow: 0 0 0 0 color-mix(in srgb, var(--bo-accent, #e11d48) 45%, transparent);
        transform: scale(1);
    }
    50% {
        box-shadow: 0 0 0 8px color-mix(in srgb, var(--bo-accent, #e11d48) 0%, transparent);
        transform: scale(1.04);
    }
}

@media (prefers-reduced-motion: reduce) {
    .pack-trigger--attention {
        animation: none;
        box-shadow: 0 0 0 2px color-mix(in srgb, var(--bo-accent, #e11d48) 55%, transparent);
    }
}
</style>
