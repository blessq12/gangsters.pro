<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { useRouter } from "vue-router";
import {
    getCompany,
    listBranches,
    listDocuments,
    storeCompany,
    updateCompany,
} from "../../../api/company.js";
import ShellIcon from "../../../shell/components/ShellIcon.vue";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";
import { useApi } from "../../../shell/composables/useApi.js";
import { usePagedList } from "../../../shell/composables/usePagedList.js";
import { useToast } from "../../../shell/composables/useToast.js";

const router = useRouter();
const toast = useToast();
const loadApi = useApi();
const saveApi = useApi();

const tab = ref("company");
const companyExists = ref(false);
const {
    page: branchesPage,
    perPage: branchesPerPage,
    total: branchesTotal,
    items: branches,
    offset: branchesOffset,
    applyPayload: applyBranches,
} = usePagedList();
const {
    page: documentsPage,
    perPage: documentsPerPage,
    total: documentsTotal,
    items: documents,
    offset: documentsOffset,
    applyPayload: applyDocuments,
} = usePagedList();

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
    full_name: "",
    inn: "",
    ogrn: "",
});
const companyErrors = reactive({});

const tabs = [
    { id: "company", label: "Компания" },
    { id: "branches", label: "Филиалы" },
    { id: "documents", label: "Документы" },
];

const branchColumns = [
    { key: "name", label: "Название" },
    { key: "phone", label: "Телефон", width: "160px" },
    { key: "address", label: "Адрес" },
    { key: "is_active", label: "Активен", width: "100px" },
    { key: "actions", label: "", width: "64px" },
];

const documentColumns = [
    { key: "slug", label: "Slug" },
    { key: "name", label: "Название" },
    { key: "actions", label: "", width: "64px" },
];

const branchRows = computed(() =>
    branches.value.map((row) => ({
        ...row,
        phone: row.phone || "—",
        address: [row.city, row.street, row.house].filter(Boolean).join(", ") || "—",
    })),
);

function clearErrors(target) {
    Object.keys(target).forEach((key) => {
        delete target[key];
    });
}

function applyCompany(payload) {
    const company = payload?.company ?? null;
    companyExists.value = Boolean(company?.id);
    companyForm.name = company?.name ?? "";
    companyForm.description = company?.description ?? "";
    companyForm.phone = company?.phone ?? "";
    companyForm.email = company?.email ?? "";
    companyForm.telegram = company?.socials?.telegram ?? "";
    companyForm.vk = company?.socials?.vk ?? "";
    companyForm.inst = company?.socials?.inst ?? "";
    companyForm.site_url = company?.socials?.site_url ?? "";
    companyForm.whatsapp = company?.socials?.whatsapp ?? "";
    const legal = payload?.legal ?? {};
    companyForm.full_name = legal.full_name ?? "";
    companyForm.inn = legal.inn ?? "";
    companyForm.ogrn = legal.ogrn ?? "";
}

async function loadCompany() {
    try {
        const payload = await loadApi.run(() => getCompany());
        applyCompany(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить компанию");
    }
}

async function loadBranches() {
    try {
        const payload = await loadApi.run(() =>
            listBranches({
                offset: branchesOffset.value,
                limit: branchesPerPage,
            }),
        );
        applyBranches(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить филиалы");
    }
}

async function loadDocuments() {
    try {
        const payload = await loadApi.run(() =>
            listDocuments({
                offset: documentsOffset.value,
                limit: documentsPerPage,
            }),
        );
        applyDocuments(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить документы");
    }
}

async function loadAll() {
    await Promise.all([loadCompany(), loadBranches(), loadDocuments()]);
}

function validateCompany() {
    clearErrors(companyErrors);
    if (!String(companyForm.name).trim()) {
        companyErrors.name = "Обязательное поле";
    }
    return Object.keys(companyErrors).length === 0;
}

async function saveCompanyForm() {
    if (!validateCompany()) {
        toast.error("Проверьте обязательные поля");
        return;
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
        legal: {
            full_name: companyForm.full_name.trim() || null,
            inn: companyForm.inn.trim() || null,
            ogrn: companyForm.ogrn.trim() || null,
        },
    };

    try {
        await saveApi.run(() => (companyExists.value ? updateCompany(body) : storeCompany(body)));
        toast.success("Компания сохранена");
        await loadCompany();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка сохранения");
    }
}

function openCreate(kind) {
    if (kind === "branch") {
        router.push({ name: "owner-company-branch", params: { id: "new" } });
        return;
    }
    router.push({ name: "owner-company-document", params: { id: "new" } });
}

function openEdit(kind, row) {
    if (kind === "branch") {
        router.push({ name: "owner-company-branch", params: { id: row.id } });
        return;
    }
    router.push({ name: "owner-company-document", params: { id: row.id } });
}

watch(branchesPage, loadBranches);
watch(documentsPage, loadDocuments);
onMounted(loadAll);
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <UiCard title="Компания">
            <div class="mb-8 flex flex-wrap gap-2">
                <button
                    v-for="item in tabs"
                    :key="item.id"
                    type="button"
                    class="h-10 px-4 text-sm font-medium transition-colors duration-150"
                    :class="
                        tab === item.id
                            ? 'bg-bo-accent text-white'
                            : 'bg-white/[0.04] text-bo-muted hover:bg-white/[0.08] hover:text-bo-fg'
                    "
                    @click="tab = item.id"
                >
                    {{ item.label }}
                </button>
            </div>

            <div v-if="tab === 'company'" class="space-y-5">
                <UiFormField label="Название" required :error="companyErrors.name">
                    <UiInput v-model="companyForm.name" />
                </UiFormField>
                <UiFormField label="Описание">
                    <textarea
                        v-model="companyForm.description"
                        rows="3"
                        class="mt-1 w-full border border-bo-border bg-bo-field px-4 py-2.5 transition-colors hover:border-bo-border-strong focus:border-bo-accent focus:outline-none"
                    />
                </UiFormField>
                <UiFormField label="Телефон">
                    <UiInput v-model="companyForm.phone" type="tel" />
                </UiFormField>
                <UiFormField label="Email">
                    <UiInput v-model="companyForm.email" type="email" />
                </UiFormField>
                <UiFormField label="Полное наименование">
                    <UiInput v-model="companyForm.full_name" />
                </UiFormField>
                <div class="grid gap-3 md:grid-cols-2">
                    <UiFormField label="ИНН">
                        <UiInput v-model="companyForm.inn" />
                    </UiFormField>
                    <UiFormField label="ОГРН">
                        <UiInput v-model="companyForm.ogrn" />
                    </UiFormField>
                </div>
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
                        <UiInput v-model="companyForm.whatsapp" type="tel" />
                    </UiFormField>
                </div>
                <UiButton :disabled="saveApi.loading" @click="saveCompanyForm">Сохранить</UiButton>
            </div>

            <div v-else-if="tab === 'branches'" class="space-y-5">
                <div class="flex justify-end">
                    <UiButton @click="openCreate('branch')">+ Филиал</UiButton>
                </div>
                <UiTable
                    :columns="branchColumns"
                    :rows="branchRows"
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
                        <UiButton
                            class="!h-9 !px-2.5"
                            title="Редактировать"
                            aria-label="Редактировать"
                            @click.stop="openEdit('branch', row)"
                        >
                            <ShellIcon name="edit" class="h-4 w-4" />
                        </UiButton>
                    </template>
                </UiTable>
                <UiPagination
                    :page="branchesPage"
                    :per-page="branchesPerPage"
                    :total="branchesTotal"
                    @update:page="branchesPage = $event"
                />
            </div>

            <div v-else class="space-y-5">
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
                        <UiButton
                            class="!h-9 !px-2.5"
                            title="Редактировать"
                            aria-label="Редактировать"
                            @click.stop="openEdit('document', row)"
                        >
                            <ShellIcon name="edit" class="h-4 w-4" />
                        </UiButton>
                    </template>
                </UiTable>
                <UiPagination
                    :page="documentsPage"
                    :per-page="documentsPerPage"
                    :total="documentsTotal"
                    @update:page="documentsPage = $event"
                />
            </div>
        </UiCard>
    </div>
</template>
