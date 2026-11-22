<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
    getBanner,
    storeBanner,
    updateBanner,
} from "../../../api/marketing.js";
import MarketingImageField from "../components/marketing/MarketingImageField.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiToggle from "../../../shell/components/ui/UiToggle.vue";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";

const route = useRoute();
const router = useRouter();
const toast = useToast();
const loadApi = useApi();
const saveApi = useApi();

const isNew = computed(() => route.params.id === "new");
const bannerId = computed(() => (isNew.value ? null : Number(route.params.id)));

const fieldErrors = reactive({});
const form = reactive({
    isActive: true,
});
const currentDesktopUrl = ref(null);
const currentMobileUrl = ref(null);
/** @type {import('vue').Ref<File|null>} */
const desktopFile = ref(null);
/** @type {import('vue').Ref<File|null>} */
const mobileFile = ref(null);

const title = computed(() => (isNew.value ? "Новый баннер" : `Баннер #${bannerId.value}`));

function clearErrors() {
    Object.keys(fieldErrors).forEach((key) => {
        delete fieldErrors[key];
    });
}

function applyDetail(payload) {
    form.isActive = Boolean(payload?.is_active ?? true);
    currentDesktopUrl.value = payload?.image_desktop ?? null;
    currentMobileUrl.value = payload?.image_mobile ?? null;
    desktopFile.value = null;
    mobileFile.value = null;
}

async function load() {
    clearErrors();
    if (isNew.value) {
        applyDetail(null);
        return;
    }
    try {
        const payload = await loadApi.run(() => getBanner(bannerId.value));
        applyDetail(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить баннер");
        router.push({ name: "owner-marketing", query: { tab: "banners" } });
    }
}

function validate() {
    clearErrors();
    if (isNew.value && !(desktopFile.value instanceof File)) {
        fieldErrors.image_desktop = "Нужна картинка для десктопа";
    }
    if (isNew.value && !(mobileFile.value instanceof File)) {
        fieldErrors.image_mobile = "Нужна картинка для телефона";
    }
    return Object.keys(fieldErrors).length === 0;
}

async function save() {
    if (!validate()) {
        toast.error("Проверьте обязательные поля");
        return;
    }
    try {
        const payload = { is_active: Boolean(form.isActive) };
        const files = {
            desktop: desktopFile.value,
            mobile: mobileFile.value,
        };
        const result = await saveApi.run(async () => (
            bannerId.value
                ? updateBanner(bannerId.value, payload, files)
                : storeBanner(payload, files)
        ));
        toast.success(bannerId.value ? "Сохранено" : "Создано");
        if (isNew.value && result?.id) {
            await router.replace({ name: "owner-marketing-banner", params: { id: result.id } });
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
                    @click="router.push({ name: 'owner-marketing', query: { tab: 'banners' } })"
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

        <p v-if="loadApi.loading" class="text-sm text-bo-muted">
            Загрузка…
        </p>

        <div v-else class="space-y-4">
            <UiCard title="Основное">
                <UiFormField label="Статус">
                    <UiToggle
                        v-model="form.isActive"
                        label-on="Активен"
                        label-off="Выключен"
                        :disabled="saveApi.loading"
                    />
                </UiFormField>
            </UiCard>

            <UiCard title="Изображения">
                <div class="grid gap-6 lg:grid-cols-2">
                    <MarketingImageField
                        v-model="desktopFile"
                        label="Десктоп"
                        :current-url="currentDesktopUrl"
                        :required="isNew"
                        :error="fieldErrors.image_desktop"
                        :disabled="saveApi.loading"
                    />
                    <MarketingImageField
                        v-model="mobileFile"
                        label="Мобильный"
                        :current-url="currentMobileUrl"
                        :required="isNew"
                        :error="fieldErrors.image_mobile"
                        :disabled="saveApi.loading"
                    />
                </div>
            </UiCard>
        </div>
    </div>
</template>
