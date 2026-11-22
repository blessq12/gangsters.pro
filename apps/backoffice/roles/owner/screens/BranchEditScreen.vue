<script setup>
import { computed, onMounted, reactive, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
    getBranch,
    storeBranch,
    updateBranch,
} from "../../../api/company.js";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";
import UiToggle from "../../../shell/components/ui/UiToggle.vue";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";
import BranchAddressMapPicker from "../components/company/BranchAddressMapPicker.vue";
import {
    emptyScheduleRows,
    rowsToSchedule,
    scheduleToRows,
    validateScheduleRows,
} from "../components/company/scheduleForm.js";

const route = useRoute();
const router = useRouter();
const toast = useToast();
const loadApi = useApi();
const saveApi = useApi();

const isNew = computed(() => route.params.id === "new");
const branchId = computed(() => (isNew.value ? null : Number(route.params.id)));

const fieldErrors = reactive({});
const form = reactive({
    name: "",
    isActive: true,
    schedule: emptyScheduleRows(),
    phone: "",
    city: "",
    street: "",
    house: "",
    comment: "",
    latitude: "",
    longitude: "",
});

const title = computed(() => {
    if (isNew.value) {
        return "Новый филиал";
    }
    const name = String(form.name ?? "").trim();
    return name !== "" ? name : "Филиал";
});

function clearErrors() {
    Object.keys(fieldErrors).forEach((key) => {
        delete fieldErrors[key];
    });
}

function applyDetail(payload) {
    form.name = payload?.name ?? "";
    form.isActive = payload?.is_active ?? true;
    form.schedule = scheduleToRows(payload?.schedule);
    form.phone = payload?.phone ?? "";
    form.city = payload?.city ?? "";
    form.street = payload?.street ?? "";
    form.house = payload?.house ?? "";
    form.comment = payload?.comment ?? "";
    form.latitude = payload?.latitude != null ? String(payload.latitude) : "";
    form.longitude = payload?.longitude != null ? String(payload.longitude) : "";
}

async function load() {
    clearErrors();
    if (isNew.value) {
        applyDetail(null);
        return;
    }
    try {
        const payload = await loadApi.run(() => getBranch(branchId.value));
        applyDetail(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить филиал");
        router.push({ name: "owner-company" });
    }
}

function validate() {
    clearErrors();
    if (!String(form.name).trim()) {
        fieldErrors.name = "Обязательное поле";
    }
    const scheduleError = validateScheduleRows(form.schedule);
    if (scheduleError) {
        fieldErrors.schedule = scheduleError;
    }
    const hasLatitude = String(form.latitude).trim() !== "";
    const hasLongitude = String(form.longitude).trim() !== "";
    if (hasLatitude !== hasLongitude) {
        fieldErrors.address = "Выбери точку на карте";
    } else if (!hasLatitude || !String(form.street).trim()) {
        fieldErrors.address = "Нужен адрес с карты (улица и координаты)";
    }
    return Object.keys(fieldErrors).length === 0;
}

/**
 * @param {{
 *   city: string,
 *   street: string,
 *   house: string,
 *   latitude: number,
 *   longitude: number,
 * }} address
 */
function onAddressMapUpdate(address) {
    form.city = address.city ?? "";
    form.street = address.street ?? "";
    form.house = address.house ?? "";
    form.latitude = address.latitude != null ? String(address.latitude) : "";
    form.longitude = address.longitude != null ? String(address.longitude) : "";
    if (fieldErrors.address) {
        delete fieldErrors.address;
    }
}

async function save() {
    if (!validate()) {
        toast.error("Проверьте обязательные поля");
        return;
    }
    try {
        const body = {
            name: form.name.trim(),
            is_active: Boolean(form.isActive),
            schedule: rowsToSchedule(form.schedule),
            phone: form.phone.trim() || null,
            city: form.city.trim() || null,
            street: form.street.trim() || null,
            house: form.house.trim() || null,
            comment: form.comment.trim() || null,
            latitude: String(form.latitude).trim() === "" ? null : Number(form.latitude),
            longitude: String(form.longitude).trim() === "" ? null : Number(form.longitude),
        };
        const result = await saveApi.run(async () => (
            branchId.value ? updateBranch(branchId.value, body) : storeBranch(body)
        ));
        toast.success(branchId.value ? "Сохранено" : "Создано");
        if (isNew.value && result?.id) {
            await router.replace({ name: "owner-company-branch", params: { id: result.id } });
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
            <div class="flex flex-wrap items-center gap-3">
                <UiToggle
                    v-model="form.isActive"
                    label-on="Активен"
                    label-off="Неактивен"
                    :disabled="loadApi.loading"
                />
                <UiButton :disabled="saveApi.loading || loadApi.loading" @click="save">
                    Сохранить
                </UiButton>
            </div>
        </div>

        <p v-if="loadApi.loading" class="text-sm text-bo-muted">
            Загрузка…
        </p>

        <div v-else class="space-y-4">
            <UiCard title="Основное">
                <div class="space-y-4">
                    <UiFormField label="Название" required :error="fieldErrors.name">
                        <UiInput v-model="form.name" />
                    </UiFormField>
                    <UiFormField label="Телефон" :error="fieldErrors.phone">
                        <UiInput
                            v-model="form.phone"
                            type="tel"
                            placeholder="+7 (XXX) XXX-XX-XX"
                        />
                    </UiFormField>
                </div>
            </UiCard>

            <UiCard title="Адрес">
                <div class="space-y-4">
                    <div class="text-sm text-bo-fg">
                        <div class="mb-2 block text-xs font-medium uppercase tracking-[0.1em] text-bo-muted">
                            Точка на карте
                            <span class="text-bo-accent">*</span>
                        </div>
                        <BranchAddressMapPicker
                            :city="form.city"
                            :street="form.street"
                            :house="form.house"
                            :latitude="form.latitude"
                            :longitude="form.longitude"
                            @update="onAddressMapUpdate"
                        />
                        <span
                            v-if="fieldErrors.address"
                            class="mt-2 block text-xs text-red-300"
                        >
                            {{ fieldErrors.address }}
                        </span>
                    </div>
                    <UiFormField label="Комментарий к адресу">
                        <UiInput
                            v-model="form.comment"
                            placeholder="Подъезд, ориентир…"
                        />
                    </UiFormField>
                </div>
            </UiCard>

            <UiCard title="Расписание">
                <p
                    v-if="fieldErrors.schedule"
                    class="mb-3 text-sm text-red-400"
                >
                    {{ fieldErrors.schedule }}
                </p>
                <div class="space-y-2">
                    <div
                        v-for="row in form.schedule"
                        :key="row.key"
                        class="grid grid-cols-1 items-center gap-2 border border-bo-border bg-bo-field px-3 py-2 sm:grid-cols-[minmax(0,1.2fr)_auto_minmax(0,0.8fr)_minmax(0,0.8fr)]"
                    >
                        <div class="text-sm text-bo-fg">
                            {{ row.label }}
                        </div>
                        <UiToggle
                            v-model="row.is_day_off"
                            label-on="Выходной"
                            label-off="Рабочий"
                        />
                        <UiInput
                            v-model="row.open"
                            type="time"
                            :disabled="row.is_day_off"
                        />
                        <UiInput
                            v-model="row.close"
                            type="time"
                            :disabled="row.is_day_off"
                        />
                    </div>
                </div>
            </UiCard>
        </div>
    </div>
</template>
