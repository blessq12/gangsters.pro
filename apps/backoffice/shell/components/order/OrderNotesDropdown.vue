<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { addOrderNote, getOrder } from "../../../api/orders.js";
import { useApi } from "../../composables/useApi.js";
import { useToast } from "../../composables/useToast.js";
import { useAuthStore } from "../../store/authStore.js";
import ShellIcon from "../ShellIcon.vue";
import UiButton from "../ui/UiButton.vue";

const props = defineProps({
    orderId: {
        type: [Number, String],
        required: true,
    },
    /** Стартовый снимок, если уже есть на заказе */
    initialNotes: {
        type: Array,
        default: () => [],
    },
});

const toast = useToast();
const auth = useAuthStore();
const loadApi = useApi();
const postApi = useApi();
const notes = ref([]);
const body = ref("");
const open = ref(false);
const rootEl = ref(/** @type {HTMLElement|null} */ (null));
const threadEl = ref(/** @type {HTMLElement|null} */ (null));

const count = computed(() => notes.value.length);
const myEmployeeId = computed(() => {
    const id = auth.employee?.id;
    return id != null ? Number(id) : null;
});

/** Хронология как в чате: старые сверху */
const threadNotes = computed(() => [...notes.value].reverse());

/**
 * @param {{ author_employee_id?: number|null }} note
 */
function isMine(note) {
    const mine = myEmployeeId.value;
    if (mine == null || note.author_employee_id == null) {
        return false;
    }
    return Number(note.author_employee_id) === mine;
}

async function scrollThreadToBottom() {
    await nextTick();
    if (threadEl.value) {
        threadEl.value.scrollTop = threadEl.value.scrollHeight;
    }
}

async function loadNotes() {
    if (!props.orderId) {
        notes.value = [];
        return;
    }
    try {
        const order = await loadApi.run(() => getOrder(props.orderId));
        notes.value = Array.isArray(order?.notes) ? order.notes : [];
        if (open.value) {
            await scrollThreadToBottom();
        }
    } catch (err) {
        notes.value = [];
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить комментарии");
    }
}

function toggle() {
    open.value = !open.value;
    if (open.value) {
        loadNotes();
    }
}

function close() {
    open.value = false;
}

async function submit() {
    const text = body.value.trim();
    if (!text) {
        return;
    }
    try {
        const payload = await postApi.run(() => addOrderNote(props.orderId, { body: text }));
        if (payload?.note) {
            notes.value = [payload.note, ...notes.value];
        } else {
            await loadNotes();
        }
        body.value = "";
        toast.success("Комментарий добавлен");
        await scrollThreadToBottom();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось отправить");
    }
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

watch(
    () => props.orderId,
    () => {
        close();
        if (Array.isArray(props.initialNotes) && props.initialNotes.length > 0) {
            notes.value = [...props.initialNotes];
        } else {
            loadNotes();
        }
    },
    { immediate: true },
);

watch(
    () => props.initialNotes,
    (value) => {
        if (!open.value && Array.isArray(value)) {
            notes.value = [...value];
        }
    },
);

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
            class="relative inline-flex h-11 items-center gap-2 border border-bo-border bg-white/10 px-3 text-bo-fg hover:bg-white/[0.08]"
            :aria-expanded="open"
            aria-label="Комментарии к заказу"
            @click.stop="toggle"
        >
            <ShellIcon
                name="chat"
                class="h-5 w-5"
            />
            <span
                class="inline-flex min-w-[1.25rem] items-center justify-center rounded-sm px-1.5 text-xs font-semibold"
                :class="count > 0
                    ? 'bg-bo-accent/25 text-bo-fg'
                    : 'bg-white/10 text-bo-muted'"
            >
                {{ count }}
            </span>
        </button>

        <div
            v-if="open"
            class="absolute right-0 z-40 mt-2 w-[22rem] max-w-[calc(100vw-2rem)] border border-bo-border bg-bo-elevated p-3 shadow-bo-lg sm:w-[26rem]"
            @click.stop
        >
            <div class="mb-3 flex items-center justify-between gap-2">
                <p class="text-sm font-medium text-bo-fg">
                    Комментарии
                </p>
                <button
                    type="button"
                    class="text-bo-muted hover:text-bo-fg"
                    aria-label="Закрыть"
                    @click="close"
                >
                    ×
                </button>
            </div>

            <p
                v-if="loadApi.loading"
                class="mb-3 text-sm text-bo-muted"
            >
                Загрузка…
            </p>
            <div
                v-else
                ref="threadEl"
                class="mb-3 max-h-72 space-y-2 overflow-y-auto text-sm"
            >
                <p
                    v-if="threadNotes.length === 0"
                    class="text-bo-muted"
                >
                    Пока пусто
                </p>
                <div
                    v-for="note in threadNotes"
                    :key="note.id"
                    class="flex"
                    :class="isMine(note) ? 'justify-end' : 'justify-start'"
                >
                    <div
                        class="w-fit max-w-[85%] px-3 py-2"
                        :class="isMine(note)
                            ? 'rounded-2xl rounded-br-md bg-bo-accent/25 text-bo-fg'
                            : 'rounded-2xl rounded-bl-md bg-black/25 text-bo-fg'"
                    >
                        <p class="whitespace-pre-wrap break-words">
                            {{ note.body }}
                        </p>
                        <p
                            class="mt-1 text-[0.7rem] leading-tight"
                            :class="isMine(note) ? 'text-bo-fg/60 text-right' : 'text-bo-muted'"
                        >
                            <span v-if="note.author_employee_id && !isMine(note)">
                                #{{ note.author_employee_id }} ·
                            </span>
                            {{ note.created_at }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex gap-2">
                <input
                    v-model="body"
                    class="min-w-0 flex-1 border border-bo-border bg-bo-field px-3 py-2 text-sm text-bo-fg"
                    type="text"
                    maxlength="4000"
                    placeholder="Новый комментарий…"
                    @keydown.enter.prevent="submit"
                >
                <UiButton
                    :disabled="postApi.loading || !body.trim()"
                    @click="submit"
                >
                    Отправить
                </UiButton>
            </div>
        </div>
    </div>
</template>
