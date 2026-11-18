<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import {
    getCompany,
    storeBranch,
    storeCompany,
    storeDocument,
    storeKitchen,
    updateBranch,
    updateCompany,
    updateDocument,
    updateKitchen,
} from "../../../api/company.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";
import UiModal from "../../../shell/components/ui/UiModal.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";

const toast = useToast();
const loadApi = useApi();
const saveApi = useApi();

const tab = ref("company");
const companyExists = ref(false);
const companyId = ref(null);
const branches = ref([]);
const kitchens = ref([]);
const documents = ref([]);

const companyForm = reactive({
    name: "",
    description: "",
    phone: "",
    email: "",
    telegram: "",
    vk: "",
    inst: "",
    site_url: "",
    whatsapp: "",
    scheduleJson: "",
});
const companyErrors = reactive({});

const modalOpen = ref(false);
const modalKind = ref("branch");
const editingId = ref(null);
const fieldErrors = reactive({});
const form = reactive({
    name: "",
    is_active: true,
    branch_id: "",
    city: "",
    street: "",
    house: "",
    comment: "",
    latitude: "",
    longitude: "",
    slug: "",
    content: "",
});

const tabs = [
    { id: "company", label: "Компания" },
    { id: "branches", label: "Филиалы" },
    { id: "kitchens", label: "Кухни" },
    { id: "documents", label: "Документы" },
];

const branchColumns = [
    { key: "id", label: "ID", width: "64px" },
    { key: "name", label: "Название" },
    { key: "is_active", label: "Активен", width: "100px" },
    { key: "actions", label: "", width: "120px" },
];

const kitchenColumns = [
    { key: "id", label: "ID", width: "64px" },
    { key: "branch_id", label: "Филиал", width: "90px" },
    { key: "address", label: "Адрес" },
    { key: "actions", label: "", width: "120px" },
];

const documentColumns = [
    { key: "slug", label: "Slug" },
    { key: "name", label: "Название" },
    { key: "actions", label: "", width: "120px" },
];

const kitchenRows = computed(() =>
    kitchens.value.map((row) => ({
        ...row,
        address: [row.city, row.street, row.house].filter(Boolean).join(", ") || "—",
    })),
);

const modalTitle = computed(() => {
    const labels = {
        branch: "филиал",
        kitchen: "кухню",
        document: "документ",
    };
    const noun = labels[modalKind.value] || "запись";
    return editingId.value ? `Редактировать ${noun}` : `Новый ${noun}`;
});

function clearErrors(target) {
    Object.keys(target).forEach((key) => {
        delete target[key];
    });
}

function resetModalForm() {
    form.name = "";
    form.is_active = true;
    form.branch_id = "";
    form.city = "";
    form.street = "";
    form.house = "";
    form.comment = "";
    form.latitude = "";
    form.longitude = "";
    form.slug = "";
    form.content = "";
    clearErrors(fieldErrors);
}

function applyCompany(payload) {
    const company = payload?.company ?? null;
    companyExists.value = Boolean(company?.id);
    companyId.value = company?.id ?? null;
    companyForm.name = company?.name ?? "";
    companyForm.description = company?.description ?? "";
    companyForm.phone = company?.phone ?? "";
    companyForm.email = company?.email ?? "";
    companyForm.telegram = company?.socials?.telegram ?? "";
    companyForm.vk = company?.socials?.vk ?? "";
    companyForm.inst = company?.socials?.inst ?? "";
    companyForm.site_url = company?.socials?.site_url ?? "";
    companyForm.whatsapp = company?.socials?.whatsapp ?? "";
    companyForm.scheduleJson = company?.schedule
        ? JSON.stringify(company.schedule, null, 2)
        : "";
    branches.value = payload?.branches ?? [];
    kitchens.value = payload?.kitchens ?? [];
    documents.value = payload?.documents ?? [];
}

async function loadCompany() {
    try {
        const payload = await loadApi.run(() => getCompany());
        applyCompany(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить компанию");
    }
}

function validateCompany() {
    clearErrors(companyErrors);
    if (!String(companyForm.name).trim()) {
        companyErrors.name = "Обязательное поле";
    }
    if (companyForm.scheduleJson.trim()) {
        try {
            JSON.parse(companyForm.scheduleJson);
        } catch {
            companyErrors.schedule = "Невалидный JSON";
        }
    }
    return Object.keys(companyErrors).length === 0;
}

async function saveCompanyForm() {
    if (!validateCompany()) {
        toast.error("Проверьте обязательные поля");
        return;
    }

    let schedule = {};
    if (companyForm.scheduleJson.trim()) {
        schedule = JSON.parse(companyForm.scheduleJson);
    }

    const body = {
        name: companyForm.name.trim(),
        description: companyForm.description || null,
        phone: companyForm.phone || null,
        email: companyForm.email || null,
        socials: {
            telegram: companyForm.telegram || null,
            vk: companyForm.vk || null,
            inst: companyForm.inst || null,
            site_url: companyForm.site_url || null,
            whatsapp: companyForm.whatsapp || null,
        },
        schedule,
    };

    try {
        await saveApi.run(() =>
            companyExists.value ? updateCompany(body) : storeCompany(body),
        );
        toast.success("Компания сохранена");
        await loadCompany();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка сохранения");
    }
}

function openCreate(kind) {
    modalKind.value = kind;
    editingId.value = null;
    resetModalForm();
    if (kind === "kitchen" && branches.value[0]) {
        form.branch_id = String(branches.value[0].id);
    }
    modalOpen.value = true;
}

function openEdit(kind, row) {
    modalKind.value = kind;
    editingId.value = row.id;
    resetModalForm();
    if (kind === "branch") {
        form.name = row.name ?? "";
        form.is_active = Boolean(row.is_active);
    } else if (kind === "kitchen") {
        form.branch_id = String(row.branch_id ?? "");
        form.city = row.city ?? "";
        form.street = row.street ?? "";
        form.house = row.house ?? "";
        form.comment = row.comment ?? "";
        form.latitude = row.latitude != null ? String(row.latitude) : "";
        form.longitude = row.longitude != null ? String(row.longitude) : "";
    } else if (kind === "document") {
        form.slug = row.slug ?? "";
        form.name = row.name ?? "";
        form.content = row.content ?? "";
    }
    modalOpen.value = true;
}

function validateModal() {
    clearErrors(fieldErrors);
    if (modalKind.value === "branch" || modalKind.value === "document") {
        if (!String(form.name).trim()) {
            fieldErrors.name = "Обязательное поле";
        }
    }
    if (modalKind.value === "kitchen") {
        if (!String(form.branch_id).trim()) {
            fieldErrors.branch_id = "Обязательное поле";
        }
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
            if (kind === "branch") {
                const body = {
                    name: form.name.trim(),
                    is_active: Boolean(form.is_active),
                };
                return id ? updateBranch(id, body) : storeBranch(body);
            }
            if (kind === "kitchen") {
                const body = {
                    branch_id: Number(form.branch_id),
                    city: form.city || null,
                    street: form.street || null,
                    house: form.house || null,
                    comment: form.comment || null,
                    latitude: form.latitude === "" ? null : Number(form.latitude),
                    longitude: form.longitude === "" ? null : Number(form.longitude),
                };
                return id ? updateKitchen(id, body) : storeKitchen(body);
            }
            const body = {
                name: form.name.trim(),
                content: form.content || null,
            };
            return id ? updateDocument(id, body) : storeDocument(body);
        });
        toast.success(id ? "Сохранено" : "Создано");
        modalOpen.value = false;
        await loadCompany();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка сохранения");
    }
}

onMounted(loadCompany);
</script>

<template>
    <div class="space-y-4 p-4 md:p-6">
        <UiCard title="Компания">
            <div class="mb-4 flex flex-wrap gap-2">
                <button
                    v-for="item in tabs"
                    :key="item.id"
                    type="button"
                    class="rounded px-3 py-1.5 text-sm"
                    :class="tab === item.id
                        ? 'bg-slate-900 text-white'
                        : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                    @click="tab = item.id"
                >
                    {{ item.label }}
                </button>
            </div>

            <div v-if="tab === 'company'" class="space-y-3">
                <UiFormField label="Название" required :error="companyErrors.name">
                    <UiInput v-model="companyForm.name" />
                </UiFormField>
                <UiFormField label="Описание">
                    <textarea
                        v-model="companyForm.description"
                        rows="3"
                        class="mt-1 w-full rounded border border-slate-300 bg-white px-3 py-2"
                    />
                </UiFormField>
                <UiFormField label="Телефон">
                    <UiInput v-model="companyForm.phone" />
                </UiFormField>
                <UiFormField label="Email">
                    <UiInput v-model="companyForm.email" type="email" />
                </UiFormField>
                <div class="grid gap-3 md:grid-cols-2">
                    <UiFormField label="Telegram">
                        <UiInput v-model="companyForm.telegram" />
                    </UiFormField>
                    <UiFormField label="VK">
                        <UiInput v-model="companyForm.vk" />
                    </UiFormField>
                    <UiFormField label="Instagram">
                        <UiInput v-model="companyForm.inst" />
                    </UiFormField>
                    <UiFormField label="Сайт">
                        <UiInput v-model="companyForm.site_url" />
                    </UiFormField>
                    <UiFormField label="WhatsApp">
                        <UiInput v-model="companyForm.whatsapp" />
                    </UiFormField>
                </div>
                <UiFormField label="Расписание (JSON)" :error="companyErrors.schedule">
                    <textarea
                        v-model="companyForm.scheduleJson"
                        rows="8"
                        class="mt-1 w-full rounded border border-slate-300 bg-white px-3 py-2 font-mono text-xs"
                    />
                </UiFormField>
                <UiButton :disabled="saveApi.loading" @click="saveCompanyForm">
                    Сохранить
                </UiButton>
            </div>

            <div v-else-if="tab === 'branches'" class="space-y-3">
                <div class="flex justify-end">
                    <UiButton @click="openCreate('branch')">+ Филиал</UiButton>
                </div>
                <UiTable
                    :columns="branchColumns"
                    :rows="branches"
                    :loading="loadApi.loading"
                    empty-text="Нет филиалов"
                >
                    <template #cell-is_active="{ value }">
                        <UiBadge
                            :variant="value ? 'success' : 'neutral'"
                            :text="value ? 'да' : 'нет'"
                        />
                    </template>
                    <template #cell-actions="{ row }">
                        <UiButton @click.stop="openEdit('branch', row)">Редактировать</UiButton>
                    </template>
                </UiTable>
            </div>

            <div v-else-if="tab === 'kitchens'" class="space-y-3">
                <div class="flex justify-end">
                    <UiButton @click="openCreate('kitchen')">+ Кухня</UiButton>
                </div>
                <UiTable
                    :columns="kitchenColumns"
                    :rows="kitchenRows"
                    :loading="loadApi.loading"
                    empty-text="Нет кухонь"
                >
                    <template #cell-actions="{ row }">
                        <UiButton @click.stop="openEdit('kitchen', row)">Редактировать</UiButton>
                    </template>
                </UiTable>
            </div>

            <div v-else class="space-y-3">
                <div class="flex justify-end">
                    <UiButton @click="openCreate('document')">+ Документ</UiButton>
                </div>
                <UiTable
                    :columns="documentColumns"
                    :rows="documents"
                    :loading="loadApi.loading"
                    empty-text="Нет документов"
                >
                    <template #cell-actions="{ row }">
                        <UiButton @click.stop="openEdit('document', row)">Редактировать</UiButton>
                    </template>
                </UiTable>
            </div>
        </UiCard>

        <UiModal :open="modalOpen" :title="modalTitle" @close="modalOpen = false">
            <div class="space-y-3">
                <template v-if="modalKind === 'branch'">
                    <UiFormField label="Название" required :error="fieldErrors.name">
                        <UiInput v-model="form.name" />
                    </UiFormField>
                    <UiFormField label="Активен">
                        <input v-model="form.is_active" type="checkbox" class="mt-2">
                    </UiFormField>
                </template>

                <template v-else-if="modalKind === 'kitchen'">
                    <UiFormField label="Филиал (branch_id)" required :error="fieldErrors.branch_id">
                        <select
                            v-model="form.branch_id"
                            class="mt-1 w-full rounded border border-slate-300 bg-white px-3 py-2"
                        >
                            <option value="">—</option>
                            <option
                                v-for="branch in branches"
                                :key="branch.id"
                                :value="String(branch.id)"
                            >
                                {{ branch.id }} — {{ branch.name }}
                            </option>
                        </select>
                    </UiFormField>
                    <UiFormField label="Город">
                        <UiInput v-model="form.city" />
                    </UiFormField>
                    <UiFormField label="Улица">
                        <UiInput v-model="form.street" />
                    </UiFormField>
                    <UiFormField label="Дом">
                        <UiInput v-model="form.house" />
                    </UiFormField>
                    <UiFormField label="Комментарий">
                        <UiInput v-model="form.comment" />
                    </UiFormField>
                    <div class="grid gap-3 md:grid-cols-2">
                        <UiFormField label="Широта">
                            <UiInput v-model="form.latitude" type="number" />
                        </UiFormField>
                        <UiFormField label="Долгота">
                            <UiInput v-model="form.longitude" type="number" />
                        </UiFormField>
                    </div>
                </template>

                <template v-else>
                    <UiFormField v-if="editingId" label="Slug">
                        <UiInput v-model="form.slug" disabled />
                    </UiFormField>
                    <UiFormField label="Название" required :error="fieldErrors.name">
                        <UiInput v-model="form.name" />
                    </UiFormField>
                    <UiFormField label="Контент">
                        <textarea
                            v-model="form.content"
                            rows="6"
                            class="mt-1 w-full rounded border border-slate-300 bg-white px-3 py-2"
                        />
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
