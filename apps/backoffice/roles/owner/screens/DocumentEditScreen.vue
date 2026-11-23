<script setup>
import { computed, onMounted, reactive, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
    getDocument,
    storeDocument,
    updateDocument,
} from "../../../api/company.js";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";
import UiRichTextEditor from "../../../shell/components/ui/UiRichTextEditor.vue";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";

const route = useRoute();
const router = useRouter();
const toast = useToast();
const loadApi = useApi();
const saveApi = useApi();

const isNew = computed(() => route.params.id === "new");
const documentId = computed(() => (isNew.value ? null : Number(route.params.id)));

const fieldErrors = reactive({});
const form = reactive({
    slug: "",
    name: "",
    content: "",
});

const title = computed(() => {
    if (isNew.value) {
        return "Новый документ";
    }
    const name = String(form.name ?? "").trim();
    return name !== "" ? name : "Документ";
});

function clearErrors() {
    Object.keys(fieldErrors).forEach((key) => {
        delete fieldErrors[key];
    });
}

function applyDetail(payload) {
    form.slug = payload?.slug ?? "";
    form.name = payload?.name ?? "";
    form.content = payload?.content ?? "";
}

async function load() {
    clearErrors();
    if (isNew.value) {
        applyDetail(null);
        return;
    }
    try {
        const payload = await loadApi.run(() => getDocument(documentId.value));
        applyDetail(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить документ");
        router.push({ name: "owner-company" });
    }
}

function validate() {
    clearErrors();
    if (!String(form.name).trim()) {
        fieldErrors.name = "Обязательное поле";
    }
    return Object.keys(fieldErrors).length === 0;
}

/**
 * Пустой TipTap (`<p></p>`) в null.
 * @param {string} html
 * @returns {string|null}
 */
function normalizeHtmlContent(html) {
    const text = String(html || "")
        .replace(/<[^>]*>/g, " ")
        .replace(/&nbsp;/gi, " ")
        .replace(/\s+/g, " ")
        .trim();
    return text !== "" ? html : null;
}

async function save() {
    if (!validate()) {
        toast.error("Проверьте обязательные поля");
        return;
    }
    try {
        const body = {
            name: form.name.trim(),
            content: normalizeHtmlContent(form.content),
        };
        const result = await saveApi.run(async () => (
            documentId.value ? updateDocument(documentId.value, body) : storeDocument(body)
        ));
        toast.success(documentId.value ? "Сохранено" : "Создано");
        if (isNew.value && result?.id) {
            await router.replace({ name: "owner-company-document", params: { id: result.id } });
            return;
        }
        await load();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка сохранения");
    }
}

watch(() => route.params.id, load);
onMounted(load);
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-4 px-4 py-5 sm:px-6 lg:px-10 lg:py-7">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
                <UiButton
                    class="!bg-white/10 !text-bo-fg"
                    @click="router.push({ name: 'owner-company' })"
                >
                    К компании
                </UiButton>
                <h1 class="text-lg font-semibold tracking-tight text-bo-fg">
                    {{ title }}
                </h1>
            </div>
            <UiButton :disabled="saveApi.loading || loadApi.loading" @click="save">
                Сохранить
            </UiButton>
        </div>

        <p v-if="loadApi.loading" class="text-sm text-bo-muted">
            Загрузка…
        </p>

        <div v-else class="space-y-4">
            <UiCard title="Основное">
                <div class="space-y-4">
                    <UiFormField
                        v-if="!isNew"
                        label="Slug"
                    >
                        <UiInput v-model="form.slug" disabled />
                    </UiFormField>
                    <UiFormField label="Название" required :error="fieldErrors.name">
                        <UiInput v-model="form.name" />
                    </UiFormField>
                    <UiFormField label="Контент">
                        <UiRichTextEditor
                            v-model="form.content"
                            :disabled="loadApi.loading || saveApi.loading"
                        />
                    </UiFormField>
                </div>
            </UiCard>
        </div>
    </div>
</template>
