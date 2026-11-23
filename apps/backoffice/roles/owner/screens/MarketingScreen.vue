<script setup>
import { onMounted, reactive, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
    getMarketing,
    listBanners,
    listPromotions,
    saveDeliveryConditions,
} from "../../../api/marketing.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { usePagedList } from "../../../shell/composables/usePagedList.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";

const route = useRoute();
const router = useRouter();
const toast = useToast();
const loadApi = useApi();
const saveApi = useApi();

const allowedTabs = ["banners", "promotions", "conditions"];
const tab = ref(
    allowedTabs.includes(String(route.query.tab ?? ""))
        ? String(route.query.tab)
        : "banners",
);
const {
    page: bannersPage,
    perPage: bannersPerPage,
    total: bannersTotal,
    items: banners,
    offset: bannersOffset,
    applyPayload: applyBanners,
} = usePagedList();
const {
    page: promotionsPage,
    perPage: promotionsPerPage,
    total: promotionsTotal,
    items: promotions,
    offset: promotionsOffset,
    applyPayload: applyPromotions,
} = usePagedList();

const conditionsForm = reactive({
    id: null,
    body: "",
    is_active: true,
});
const conditionsErrors = reactive({});

const tabs = [
    { id: "banners", label: "Баннеры" },
    { id: "promotions", label: "Акции-медиа" },
    { id: "conditions", label: "Условия доставки" },
];

const bannerColumns = [
    { key: "id", label: "ID", width: "64px" },
    { key: "preview", label: "Превью", width: "120px" },
    { key: "sort_order", label: "Порядок", width: "90px" },
    { key: "is_active", label: "Активен", width: "100px" },
    { key: "actions", label: "", width: "140px" },
];

const promotionColumns = [
    { key: "id", label: "ID", width: "64px" },
    { key: "preview", label: "Превью", width: "120px" },
    { key: "title", label: "Название" },
    { key: "sort_order", label: "Порядок", width: "90px" },
    { key: "is_active", label: "Активна", width: "100px" },
    { key: "actions", label: "", width: "140px" },
];

function clearErrors(target) {
    Object.keys(target).forEach((key) => {
        delete target[key];
    });
}

function applyConditions(payload) {
    const conditions = payload?.delivery_conditions_text;
    conditionsForm.id = conditions?.id ?? null;
    conditionsForm.body = conditions?.body ?? "";
    conditionsForm.is_active = conditions?.is_active ?? true;
}

async function loadConditions() {
    try {
        const payload = await loadApi.run(() => getMarketing());
        applyConditions(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить условия доставки");
    }
}

async function loadBanners() {
    try {
        const payload = await loadApi.run(() => listBanners({
            offset: bannersOffset.value,
            limit: bannersPerPage,
        }));
        applyBanners(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить баннеры");
    }
}

async function loadPromotions() {
    try {
        const payload = await loadApi.run(() => listPromotions({
            offset: promotionsOffset.value,
            limit: promotionsPerPage,
        }));
        applyPromotions(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить акции");
    }
}

async function loadMarketing() {
    await Promise.all([loadConditions(), loadBanners(), loadPromotions()]);
}

function setTab(next) {
    tab.value = next;
    router.replace({
        name: "owner-marketing",
        query: next === "banners" ? {} : { tab: next },
    });
}

function openCreateBanner() {
    router.push({ name: "owner-marketing-banner", params: { id: "new" } });
}

function openEditBanner(row) {
    router.push({ name: "owner-marketing-banner", params: { id: row.id } });
}

function openCreatePromotion() {
    router.push({ name: "owner-marketing-promotion", params: { id: "new" } });
}

function openEditPromotion(row) {
    router.push({ name: "owner-marketing-promotion", params: { id: row.id } });
}

async function saveConditionsForm() {
    clearErrors(conditionsErrors);
    if (!String(conditionsForm.body).trim()) {
        conditionsErrors.body = "Обязательное поле";
        toast.error("Проверьте обязательные поля");
        return;
    }
    const body = {
        id: conditionsForm.id,
        body: conditionsForm.body,
        is_active: Boolean(conditionsForm.is_active),
    };
    try {
        await saveApi.run(() => saveDeliveryConditions(body));
        toast.success("Условия сохранены");
        await loadConditions();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка сохранения");
    }
}

watch(bannersPage, loadBanners);
watch(promotionsPage, loadPromotions);
watch(
    () => route.query.tab,
    (value) => {
        const next = String(value ?? "banners");
        if (allowedTabs.includes(next) && tab.value !== next) {
            tab.value = next;
        }
    },
);
onMounted(loadMarketing);
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <UiCard title="Маркетинг">
            <div class="mb-8 flex flex-wrap gap-2">
                <button
                    v-for="item in tabs"
                    :key="item.id"
                    type="button"
                    class="h-10 px-4 text-sm font-medium transition-colors duration-150"
                    :class="tab === item.id
                        ? 'bg-bo-accent text-white'
                        : 'bg-white/[0.04] text-bo-muted hover:bg-white/[0.08] hover:text-bo-fg'"
                    @click="setTab(item.id)"
                >
                    {{ item.label }}
                </button>
            </div>

            <div v-if="tab === 'banners'" class="space-y-5">
                <div class="flex justify-end">
                    <UiButton @click="openCreateBanner">+ Баннер</UiButton>
                </div>
                <UiTable
                    :columns="bannerColumns"
                    :rows="banners"
                    :loading="loadApi.loading"
                    empty-text="Нет баннеров"
                >
                    <template #cell-preview="{ row }">
                        <img
                            v-if="row.image_desktop || row.image_mobile"
                            :src="row.image_desktop || row.image_mobile"
                            alt=""
                            class="h-12 w-20 object-cover"
                        >
                        <span v-else class="text-bo-muted">—</span>
                    </template>
                    <template #cell-is_active="{ value }">
                        <UiBadge
                            :variant="value ? 'success' : 'neutral'"
                            :text="value ? 'да' : 'нет'"
                        />
                    </template>
                    <template #cell-actions="{ row }">
                        <UiButton @click.stop="openEditBanner(row)">Редактировать</UiButton>
                    </template>
                </UiTable>
                <UiPagination
                    :page="bannersPage"
                    :per-page="bannersPerPage"
                    :total="bannersTotal"
                    @update:page="bannersPage = $event"
                />
            </div>

            <div v-else-if="tab === 'promotions'" class="space-y-5">
                <div class="flex justify-end">
                    <UiButton @click="openCreatePromotion">+ Акция</UiButton>
                </div>
                <UiTable
                    :columns="promotionColumns"
                    :rows="promotions"
                    :loading="loadApi.loading"
                    empty-text="Нет акций"
                >
                    <template #cell-preview="{ row }">
                        <img
                            v-if="row.image"
                            :src="row.image"
                            alt=""
                            class="h-12 w-20 object-cover"
                        >
                        <span v-else class="text-bo-muted">—</span>
                    </template>
                    <template #cell-is_active="{ value }">
                        <UiBadge
                            :variant="value ? 'success' : 'neutral'"
                            :text="value ? 'да' : 'нет'"
                        />
                    </template>
                    <template #cell-actions="{ row }">
                        <UiButton @click.stop="openEditPromotion(row)">Редактировать</UiButton>
                    </template>
                </UiTable>
                <UiPagination
                    :page="promotionsPage"
                    :per-page="promotionsPerPage"
                    :total="promotionsTotal"
                    @update:page="promotionsPage = $event"
                />
            </div>

            <div v-else class="space-y-5">
                <UiFormField label="Текст условий" required :error="conditionsErrors.body">
                    <textarea
                        v-model="conditionsForm.body"
                        rows="8"
                        class="mt-1 w-full border border-bo-border bg-bo-field px-4 py-2.5 transition-colors hover:border-bo-border-strong focus:border-bo-accent focus:outline-none"
                    />
                </UiFormField>
                <UiFormField label="Активно">
                    <input v-model="conditionsForm.is_active" type="checkbox" class="mt-2">
                </UiFormField>
                <UiButton :disabled="saveApi.loading" @click="saveConditionsForm">
                    Сохранить
                </UiButton>
            </div>
        </UiCard>
    </div>
</template>
