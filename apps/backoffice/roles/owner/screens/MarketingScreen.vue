<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import {
    getMarketing,
    listBanners,
    listPromotions,
    saveDeliveryConditions,
    storeBanner,
    storePromotion,
    updateBanner,
    updatePromotion,
} from "../../../api/marketing.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { usePagedList } from "../../../shell/composables/usePagedList.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";
import UiModal from "../../../shell/components/ui/UiModal.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";

const toast = useToast();
const loadApi = useApi();
const saveApi = useApi();

const tab = ref("banners");
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

const modalOpen = ref(false);
const modalKind = ref("banner");
const editingId = ref(null);
const fieldErrors = reactive({});
const form = reactive({
    image_desktop: "",
    image_mobile: "",
    is_active: true,
    title: "",
    body: "",
    image: "",
});

const tabs = [
    { id: "banners", label: "Баннеры" },
    { id: "promotions", label: "Акции-медиа" },
    { id: "conditions", label: "Условия доставки" },
];

const bannerColumns = [
    { key: "id", label: "ID", width: "64px" },
    { key: "image_desktop", label: "Десктоп" },
    { key: "sort_order", label: "Порядок", width: "90px" },
    { key: "is_active", label: "Активен", width: "100px" },
    { key: "actions", label: "", width: "120px" },
];

const promotionColumns = [
    { key: "id", label: "ID", width: "64px" },
    { key: "title", label: "Название" },
    { key: "sort_order", label: "Порядок", width: "90px" },
    { key: "is_active", label: "Активна", width: "100px" },
    { key: "actions", label: "", width: "120px" },
];

const modalTitle = computed(() => {
    if (modalKind.value === "banner") {
        return editingId.value ? "Редактировать баннер" : "Новый баннер";
    }
    return editingId.value ? "Редактировать акцию" : "Новая акция";
});

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

function resetModalForm() {
    form.image_desktop = "";
    form.image_mobile = "";
    form.is_active = true;
    form.title = "";
    form.body = "";
    form.image = "";
    clearErrors(fieldErrors);
}

function openCreate(kind) {
    modalKind.value = kind;
    editingId.value = null;
    resetModalForm();
    modalOpen.value = true;
}

function openEdit(kind, row) {
    modalKind.value = kind;
    editingId.value = row.id;
    resetModalForm();
    if (kind === "banner") {
        form.image_desktop = row.image_desktop ?? "";
        form.image_mobile = row.image_mobile ?? "";
        form.is_active = Boolean(row.is_active);
    } else {
        form.title = row.title ?? "";
        form.body = row.body ?? "";
        form.image = row.image ?? "";
        form.is_active = Boolean(row.is_active);
    }
    modalOpen.value = true;
}

function validateModal() {
    clearErrors(fieldErrors);
    if (modalKind.value === "banner") {
        if (!editingId.value) {
            if (!String(form.image_desktop).trim()) {
                fieldErrors.image_desktop = "Обязательное поле";
            }
            if (!String(form.image_mobile).trim()) {
                fieldErrors.image_mobile = "Обязательное поле";
            }
        }
    } else if (!String(form.title).trim()) {
        fieldErrors.title = "Обязательное поле";
    }
    return Object.keys(fieldErrors).length === 0;
}

async function saveModal() {
    if (!validateModal()) {
        toast.error("Проверьте обязательные поля");
        return;
    }

    const kind = modalKind.value;
    const id = editingId.value;

    try {
        await saveApi.run(async () => {
            if (kind === "banner") {
                const body = {
                    is_active: Boolean(form.is_active),
                };
                if (form.image_desktop) {
                    body.image_desktop = form.image_desktop.trim();
                }
                if (form.image_mobile) {
                    body.image_mobile = form.image_mobile.trim();
                }
                return id ? updateBanner(id, body) : storeBanner(body);
            }
            const body = {
                title: form.title.trim(),
                body: form.body || null,
                image: form.image || null,
                is_active: Boolean(form.is_active),
            };
            return id ? updatePromotion(id, body) : storePromotion(body);
        });
        toast.success(id ? "Сохранено" : "Создано");
        modalOpen.value = false;
        await (kind === "banner" ? loadBanners() : loadPromotions());
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка сохранения");
    }
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
                    @click="tab = item.id"
                >
                    {{ item.label }}
                </button>
            </div>

            <div v-if="tab === 'banners'" class="space-y-5">
                <div class="flex justify-end">
                    <UiButton @click="openCreate('banner')">+ Баннер</UiButton>
                </div>
                <UiTable
                    :columns="bannerColumns"
                    :rows="banners"
                    :loading="loadApi.loading"
                    empty-text="Нет баннеров"
                >
                    <template #cell-is_active="{ value }">
                        <UiBadge
                            :variant="value ? 'success' : 'neutral'"
                            :text="value ? 'да' : 'нет'"
                        />
                    </template>
                    <template #cell-actions="{ row }">
                        <UiButton @click.stop="openEdit('banner', row)">Редактировать</UiButton>
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
                    <UiButton @click="openCreate('promotion')">+ Акция</UiButton>
                </div>
                <UiTable
                    :columns="promotionColumns"
                    :rows="promotions"
                    :loading="loadApi.loading"
                    empty-text="Нет акций"
                >
                    <template #cell-is_active="{ value }">
                        <UiBadge
                            :variant="value ? 'success' : 'neutral'"
                            :text="value ? 'да' : 'нет'"
                        />
                    </template>
                    <template #cell-actions="{ row }">
                        <UiButton @click.stop="openEdit('promotion', row)">Редактировать</UiButton>
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

        <UiModal :open="modalOpen" :title="modalTitle" @close="modalOpen = false">
            <div class="space-y-5">
                <template v-if="modalKind === 'banner'">
                    <UiFormField
                        label="image_desktop"
                        :required="!editingId"
                        :error="fieldErrors.image_desktop"
                    >
                        <UiInput v-model="form.image_desktop" />
                    </UiFormField>
                    <UiFormField
                        label="image_mobile"
                        :required="!editingId"
                        :error="fieldErrors.image_mobile"
                    >
                        <UiInput v-model="form.image_mobile" />
                    </UiFormField>
                    <UiFormField label="Активен">
                        <input v-model="form.is_active" type="checkbox" class="mt-2">
                    </UiFormField>
                </template>
                <template v-else>
                    <UiFormField label="Название" required :error="fieldErrors.title">
                        <UiInput v-model="form.title" />
                    </UiFormField>
                    <UiFormField label="Текст">
                        <textarea
                            v-model="form.body"
                            rows="4"
                            class="mt-1 w-full border border-bo-border bg-bo-field px-4 py-2.5 transition-colors hover:border-bo-border-strong focus:border-bo-accent focus:outline-none"
                        />
                    </UiFormField>
                    <UiFormField label="Image">
                        <UiInput v-model="form.image" />
                    </UiFormField>
                    <UiFormField label="Активна">
                        <input v-model="form.is_active" type="checkbox" class="mt-2">
                    </UiFormField>
                </template>
            </div>
            <template #footer>
                <UiButton @click="modalOpen = false">Отмена</UiButton>
                <UiButton :disabled="saveApi.loading" @click="saveModal">Сохранить</UiButton>
            </template>
        </UiModal>
    </div>
</template>
