<script setup>
import { nextTick, ref, watch } from "vue";
import { storeToRefs } from "pinia";
import { useToast } from "../../composables/useToast.js";
import UiButton from "../../components/ui/UiButton.vue";
import { useOrderNotesStore } from "./orderNotesStore.js";

const ROLE_LABELS = {
    owner: "Владелец",
    administrator: "Администратор",
    kitchen: "Кухня",
    courier: "Курьер",
};

const store = useOrderNotesStore();
const toast = useToast();
const { isOpen, orderId, loading, posting, draft, canCompose, threadNotes, scrollToken, error } =
    storeToRefs(store);

const threadEl = ref(/** @type {HTMLElement|null} */ (null));
const panelEl = ref(/** @type {HTMLElement|null} */ (null));

async function scrollThreadToBottom() {
    await nextTick();
    if (threadEl.value) {
        threadEl.value.scrollTop = threadEl.value.scrollHeight;
    }
}

watch(scrollToken, () => {
    if (isOpen.value) {
        void scrollThreadToBottom();
    }
});

watch(error, (value) => {
    if (value) {
        toast.error(value);
        store.error = null;
    }
});

watch(isOpen, (open) => {
    if (open) {
        void scrollThreadToBottom();
    }
});

/**
 * @param {string|null|undefined} role
 */
function roleLabel(role) {
    const key = String(role || "").trim();
    if (!key) {
        return "";
    }
    return ROLE_LABELS[key] || key;
}

/**
 * @param {Record<string, unknown>} note
 */
function authorLine(note) {
    const name = String(note?.author_name || "").trim();
    const role = roleLabel(note?.author_role);
    if (name && role) {
        return `${name} · ${role}`;
    }
    if (name) {
        return name;
    }
    if (role) {
        return role;
    }
    if (note?.author_employee_id) {
        return `Сотрудник #${note.author_employee_id}`;
    }
    return "";
}

/**
 * @param {string|null|undefined} value
 */
function formatNoteTime(value) {
    const raw = String(value || "").trim();
    if (!raw) {
        return "";
    }
    const date = new Date(raw);
    if (Number.isNaN(date.getTime())) {
        return raw;
    }
    return date.toLocaleString("ru-RU", {
        day: "2-digit",
        month: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
    });
}

/**
 * @param {Record<string, unknown>} note
 */
function metaLine(note) {
    const parts = [authorLine(note), formatNoteTime(note?.created_at)].filter(Boolean);
    return parts.join(" · ");
}

async function onSubmit() {
    try {
        await store.submit();
        toast.success("Комментарий добавлен");
    } catch {
        // ошибка уже в store.error → toast через watch
    }
}

/**
 * @param {MouseEvent} event
 */
function onBackdropClick(event) {
    if (!panelEl.value) {
        return;
    }
    const target = event.target;
    if (target instanceof Node && !panelEl.value.contains(target)) {
        store.close();
    }
}
</script>

<template>
    <Teleport to="body">
        <div
            v-if="isOpen"
            class="fixed inset-0 z-[60] flex justify-end bg-black/40 p-3 sm:p-4"
            role="dialog"
            aria-modal="true"
            aria-label="Комментарии к заказу"
            @click="onBackdropClick"
        >
            <div
                ref="panelEl"
                class="flex h-full w-full max-w-md flex-col border border-bo-border bg-bo-elevated shadow-bo-lg"
                @click.stop
            >
                <div
                    class="flex items-center justify-between gap-2 border-b border-bo-border px-4 py-3"
                >
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-bo-fg">Комментарии</p>
                        <p v-if="orderId != null" class="text-xs text-bo-muted">
                            Заказ #{{ orderId }}
                        </p>
                    </div>
                    <button
                        type="button"
                        class="text-bo-muted hover:text-bo-fg"
                        aria-label="Закрыть"
                        @click="store.close()"
                    >
                        ×
                    </button>
                </div>

                <div class="flex min-h-0 flex-1 flex-col px-4 py-3">
                    <p v-if="loading" class="mb-3 text-sm text-bo-muted">Загрузка…</p>
                    <div
                        v-else
                        ref="threadEl"
                        class="mb-3 min-h-0 flex-1 space-y-2 overflow-y-auto text-sm"
                    >
                        <p v-if="threadNotes.length === 0" class="text-bo-muted">Пока пусто</p>
                        <div
                            v-for="note in threadNotes"
                            :key="note.id"
                            class="flex"
                            :class="store.isMine(note) ? 'justify-end' : 'justify-start'"
                        >
                            <div
                                class="w-fit max-w-[85%] px-3 py-2"
                                :class="
                                    store.isMine(note)
                                        ? 'rounded-2xl rounded-br-md bg-bo-accent/25 text-bo-fg'
                                        : 'rounded-2xl rounded-bl-md bg-black/25 text-bo-fg'
                                "
                            >
                                <p class="whitespace-pre-wrap break-words">
                                    {{ note.body }}
                                </p>
                                <p
                                    class="mt-1 text-[0.7rem] leading-tight"
                                    :class="
                                        store.isMine(note)
                                            ? 'text-bo-fg/60 text-right'
                                            : 'text-bo-muted'
                                    "
                                >
                                    {{ metaLine(note) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-if="canCompose" class="flex gap-2">
                        <input
                            v-model="draft"
                            class="min-w-0 flex-1 border border-bo-border bg-bo-field px-3 py-2 text-sm text-bo-fg"
                            type="text"
                            maxlength="4000"
                            placeholder="Новый комментарий…"
                            @keydown.enter.prevent="onSubmit"
                        />
                        <UiButton
                            :disabled="posting || !String(draft || '').trim()"
                            @click="onSubmit"
                        >
                            Отправить
                        </UiButton>
                    </div>
                    <p v-else class="text-xs text-bo-muted">
                        К выданному заказу комментарии закрыты
                    </p>
                </div>
            </div>
        </div>
    </Teleport>
</template>
