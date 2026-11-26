<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { getPromotion, storePromotion, updatePromotion } from "../../../api/marketing.js";
import MarketingImageField from "../components/marketing/MarketingImageField.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";
import UiToggle from "../../../shell/components/ui/UiToggle.vue";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";

const route = useRoute();
const router = useRouter();
const toast = useToast();
const loadApi = useApi();
const saveApi = useApi();

const isNew = computed(() => route.params.id === "new");
const promotionId = computed(() => (isNew.value ? null : Number(route.params.id)));

const fieldErrors = reactive({});
const form = reactive({
    title: "",
    body: "",
    isActive: true,
});
const currentImageUrl = ref(null);
/** @type {import('vue').Ref<File|null>} */
const imageFile = ref(null);
const removeImage = ref(false);

const title = computed(() => {
    if (isNew.value) {
        return "Новая акция";
    }
    const name = String(form.title ?? "").trim();
    return name !== "" ? name : `Акция #${promotionId.value}`;
});

function clearErrors() {
    Object.keys(fieldErrors).forEach((key) => {
        delete fieldErrors[key];
    });
}

function applyDetail(payload) {
    form.title = payload?.title ?? "";
    form.body = payload?.body ?? "";
    form.isActive = Boolean(payload?.is_active ?? true);
    currentImageUrl.value = payload?.image ?? null;
    imageFile.value = null;
    removeImage.value = false;
}

async function load() {
    clearErrors();
    if (isNew.value) {
        applyDetail(null);
        return;
    }
    try {
        const payload = await loadApi.run(() => getPromotion(promotionId.value));
        applyDetail(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить акцию");
        router.push({ name: "owner-marketing", query: { tab: "promotions" } });
    }
}

function validate() {
    clearErrors();
    if (!String(form.title).trim()) {
        fieldErrors.title = "Обязательное поле";
    }
    return Object.keys(fieldErrors).length === 0;
}

async function save() {
    if (!validate()) {
        toast.error("Проверьте обязательные поля");
        return;
    }
    try {
        const payload = {
            title: form.title.trim(),
            body: form.body.trim() || null,
            is_active: Boolean(form.isActive),
            remove_image: Boolean(removeImage.value) && !(imageFile.value instanceof File),
        };
        const files = { image: imageFile.value };
        const result = await saveApi.run(async () =>
            promotionId.value
                ? updatePromotion(promotionId.value, payload, files)
                : storePromotion(payload, files),
        );
        toast.success(promotionId.value ? "Сохранено" : "Создано");
        if (isNew.value && result?.id) {
            await router.replace({ name: "owner-marketing-promotion", params: { id: result.id } });
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
                    @click="router.push({ name: 'owner-marketing', query: { tab: 'promotions' } })"
                >
                    К маркетингу
                </UiButton>
                <h1 class="text-lg font-semibold tracking-tight text-bo-fg">
                    {{ title }}
                </h1>
            </div>
            <UiButton :disabled="saveApi.loading || loadApi.loading" @click="save">
                Сохранить
            </UiButton>
        </div>

        <p v-if="loadApi.loading" class="text-sm text-bo-muted">Загрузка…</p>

        <div v-else class="space-y-4">
            <UiCard title="Основное">
                <div class="space-y-4">
                    <UiFormField label="Название" required :error="fieldErrors.title">
                        <UiInput v-model="form.title" />
                    </UiFormField>
                    <UiFormField label="Текст">
                        <textarea
                            v-model="form.body"
                            rows="6"
                            class="mt-1 w-full border border-bo-border bg-bo-field px-4 py-2.5 transition-colors hover:border-bo-border-strong focus:border-bo-accent focus:outline-none"
                        />
                    </UiFormField>
                    <UiFormField label="Статус">
                        <UiToggle
                            v-model="form.isActive"
                            label-on="Активна"
                            label-off="Выключена"
                            :disabled="saveApi.loading"
                        />
                    </UiFormField>
                </div>
            </UiCard>

            <UiCard title="Изображение">
                <MarketingImageField
                    v-model="imageFile"
                    v-model:marked-for-removal="removeImage"
                    label="Картинка акции"
                    :current-url="currentImageUrl"
                    clearable
                    :disabled="saveApi.loading"
                />
            </UiCard>
        </div>
    </div>
</template>
