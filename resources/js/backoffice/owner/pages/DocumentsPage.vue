<script setup>
import { onMounted, ref } from "vue";
import { backofficeRequest } from "../../api";

const documents = ref([]);
const draft = ref(empty());
const error = ref("");

function empty() {
    return { id: null, name: "", content: "" };
}

onMounted(load);

async function load() {
    error.value = "";
    try {
        const payload = await backofficeRequest("GET", "/api/backoffice/company/documents");
        documents.value = payload.documents ?? [];
    } catch (reason) {
        error.value = reason instanceof Error ? reason.message : "Не удалось связаться с сервером.";
    }
}

function edit(document) {
    draft.value = { id: document.id, name: document.name, content: document.content };
}

async function submit() {
    error.value = "";
    const body = { name: draft.value.name, content: draft.value.content };
    try {
        if (draft.value.id) {
            await backofficeRequest("PUT", `/api/backoffice/company/documents/${draft.value.id}`, body);
        } else {
            await backofficeRequest("POST", "/api/backoffice/company/documents", body);
        }
        draft.value = empty();
        await load();
    } catch (reason) {
        error.value = reason instanceof Error ? reason.message : "Не удалось связаться с сервером.";
    }
}
</script>

<template>
    <section class="space-y-4">
        <h1 class="text-xl">Документы</h1>
        <p
            v-if="error"
            class="text-sm text-red-700"
        >
            {{ error }}
        </p>
        <div class="overflow-x-auto rounded border border-neutral-200 bg-white">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-neutral-200 bg-neutral-50 text-xs text-neutral-500">
                    <tr>
                        <th class="px-3 py-2 font-medium">Название</th>
                        <th class="px-3 py-2 font-medium">Слаг</th>
                        <th class="px-3 py-2 font-medium" />
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="document in documents"
                        :key="document.id"
                        class="border-b border-neutral-100 last:border-0"
                    >
                        <td class="px-3 py-2">{{ document.name }}</td>
                        <td class="px-3 py-2">{{ document.slug }}</td>
                        <td class="px-3 py-2">
                            <button
                                type="button"
                                class="inline-flex h-9 items-center rounded border border-neutral-300 px-3 text-sm"
                                @click="edit(document)"
                            >
                                Править
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <form
            class="space-y-3"
            @submit.prevent="submit"
        >
            <h2 class="text-sm font-medium">{{ draft.id ? `Документ #${draft.id}` : "Новый документ" }}</h2>
            <input
                v-model="draft.name"
                required
                placeholder="Название"
                class="h-9 w-full rounded border border-neutral-300 px-3 text-sm"
            >
            <textarea
                v-model="draft.content"
                rows="6"
                placeholder="Содержимое"
                class="w-full rounded border border-neutral-300 px-3 py-2 text-sm"
            />
            <div class="flex gap-2">
                <button
                    type="submit"
                    class="inline-flex h-9 items-center rounded bg-neutral-900 px-3 text-sm text-white"
                >
                    Сохранить
                </button>
                <button
                    type="button"
                    class="inline-flex h-9 items-center rounded border border-neutral-300 px-3 text-sm"
                    @click="draft = empty()"
                >
                    Новый
                </button>
            </div>
        </form>
    </section>
</template>
