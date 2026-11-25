<script setup>
import { ref, watch } from "vue";
import { addOrderNote, getOrder } from "../../../api/orders.js";
import { useApi } from "../../composables/useApi.js";
import { useToast } from "../../composables/useToast.js";
import UiButton from "../ui/UiButton.vue";

const props = defineProps({
    orderId: {
        type: [Number, String],
        required: true,
    },
});

const toast = useToast();
const loadApi = useApi();
const postApi = useApi();
const notes = ref([]);
const body = ref("");

async function loadNotes() {
    if (!props.orderId) {
        notes.value = [];
        return;
    }
    try {
        const order = await loadApi.run(() => getOrder(props.orderId));
        notes.value = Array.isArray(order?.notes) ? order.notes : [];
    } catch (err) {
        notes.value = [];
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить ленту");
    }
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
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось отправить");
    }
}

watch(() => props.orderId, loadNotes, { immediate: true });
</script>

<template>
    <div class="space-y-3">
        <div class="flex gap-2">
            <input
                v-model="body"
                class="min-w-0 flex-1 border border-bo-border bg-bo-field px-3 py-2 text-sm text-bo-fg"
                type="text"
                maxlength="4000"
                placeholder="Комментарий к заказу…"
                @keydown.enter.prevent="submit"
            >
            <UiButton
                :disabled="postApi.loading || !body.trim()"
                @click="submit"
            >
                Отправить
            </UiButton>
        </div>
        <p
            v-if="loadApi.loading"
            class="text-sm text-bo-muted"
        >
            Загрузка ленты…
        </p>
        <ul
            v-else
            class="max-h-64 space-y-2 overflow-y-auto text-sm"
        >
            <li
                v-for="note in notes"
                :key="note.id"
                class="border border-bo-border bg-black/20 px-3 py-2"
            >
                <p class="text-bo-fg">
                    {{ note.body }}
                </p>
                <p class="mt-1 text-xs text-bo-muted">
                    {{ note.created_at }}
                    <span v-if="note.author_employee_id">
                        · сотрудник #{{ note.author_employee_id }}
                    </span>
                </p>
            </li>
            <li
                v-if="notes.length === 0"
                class="text-bo-muted"
            >
                Пока пусто
            </li>
        </ul>
    </div>
</template>
