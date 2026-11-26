<script setup>
import { computed, watch } from "vue";
import ShellIcon from "../../components/ShellIcon.vue";
import { useOrderNotesStore } from "./orderNotesStore.js";

const props = defineProps({
    orderId: {
        type: [Number, String],
        required: true,
    },
    /** Стартовый снимок — seed для бейджа и быстрого open */
    initialNotes: {
        type: Array,
        default: () => [],
    },
    /** Явный count, если ленты в payload нет */
    notesCount: {
        type: [Number, null],
        default: null,
    },
    /** Статус заказа — для блокировки compose на выданных */
    orderStatus: {
        type: String,
        default: "",
    },
});

const store = useOrderNotesStore();

const propCount = computed(() => {
    if (props.notesCount != null && Number.isFinite(Number(props.notesCount))) {
        return Math.max(0, Number(props.notesCount));
    }
    return Array.isArray(props.initialNotes) ? props.initialNotes.length : 0;
});

const count = computed(() => store.countFor(props.orderId, propCount.value));

const hasNotes = computed(() => count.value > 0);

const isActive = computed(() => store.isOpen && String(store.orderId) === String(props.orderId));

watch(
    () => [props.orderId, propCount.value],
    ([orderId, nextCount]) => {
        if (orderId == null || orderId === "") {
            return;
        }
        store.rememberCount(orderId, nextCount);
    },
    { immediate: true },
);

function onClick() {
    if (isActive.value) {
        store.close();
        return;
    }
    store.open({
        orderId: props.orderId,
        orderStatus: props.orderStatus,
        initialNotes: props.initialNotes,
        notesCount: props.notesCount ?? undefined,
    });
}
</script>

<template>
    <button
        type="button"
        class="order-notes-trigger relative inline-flex h-11 items-center gap-2 border px-3 transition-colors"
        :class="[
            isActive
                ? 'border-bo-accent bg-bo-accent/20 text-bo-fg'
                : hasNotes
                  ? 'order-notes-trigger--has-notes border-bo-accent/70 bg-bo-accent/15 text-bo-fg hover:bg-bo-accent/25'
                  : 'border-bo-border bg-white/10 text-bo-fg hover:bg-white/[0.08]',
        ]"
        :aria-expanded="isActive"
        :aria-label="hasNotes ? `Комментарии к заказу, ${count}` : 'Комментарии к заказу'"
        @click.stop="onClick"
    >
        <ShellIcon
            name="chat"
            class="h-5 w-5 shrink-0"
            :class="hasNotes && !isActive ? 'order-notes-trigger__icon' : ''"
        />
        <span
            class="inline-flex min-w-[1.25rem] items-center justify-center rounded-sm px-1.5 text-xs font-semibold tabular-nums"
            :class="hasNotes ? 'bg-bo-accent/35 text-bo-fg' : 'bg-white/10 text-bo-muted'"
        >
            {{ count }}
        </span>
        <span
            v-if="hasNotes && !isActive"
            class="order-notes-trigger__pulse pointer-events-none absolute inset-0"
            aria-hidden="true"
        />
    </button>
</template>

<style scoped>
.order-notes-trigger--has-notes {
    box-shadow: 0 0 0 1px color-mix(in srgb, var(--bo-accent, #c62424) 35%, transparent);
}

.order-notes-trigger__icon {
    animation: order-notes-icon-nudge 2.4s ease-in-out infinite;
}

.order-notes-trigger__pulse {
    border-radius: inherit;
    box-shadow: 0 0 0 0 color-mix(in srgb, var(--bo-accent, #c62424) 45%, transparent);
    animation: order-notes-pulse 2.4s ease-out infinite;
}

@keyframes order-notes-pulse {
    0% {
        box-shadow: 0 0 0 0 color-mix(in srgb, var(--bo-accent, #c62424) 45%, transparent);
    }
    70% {
        box-shadow: 0 0 0 8px color-mix(in srgb, var(--bo-accent, #c62424) 0%, transparent);
    }
    100% {
        box-shadow: 0 0 0 0 color-mix(in srgb, var(--bo-accent, #c62424) 0%, transparent);
    }
}

@keyframes order-notes-icon-nudge {
    0%,
    100% {
        transform: scale(1);
    }
    40% {
        transform: scale(1.08);
    }
    55% {
        transform: scale(1);
    }
}

@media (prefers-reduced-motion: reduce) {
    .order-notes-trigger__icon,
    .order-notes-trigger__pulse {
        animation: none;
    }
}
</style>
