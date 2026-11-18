<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import {
    getCatalog,
    storeCategory,
    storeProduct,
    storeSet,
    storeTag,
    updateCategory,
    updateProduct,
    updateSet,
    updateTag,
} from "../../../api/catalog.js";
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
const catalogApi = useApi();
const saveApi = useApi();

const tab = ref("products");
const catalog = reactive({
    categories: [],
    products: [],
    sets: [],
    tags: [],
});

const modalOpen = ref(false);
const modalKind = ref("product");
const editingId = ref(null);
const fieldErrors = reactive({});
const form = reactive({
    name: "",
    price: "",
    weight: "",
    status: "active",
    description: "",
    sku: "",
    is_active: true,
    is_accompanying: false,
    code: "",
    label: "",
    color: "#999999",
});

const tabs = [
    { id: "categories", label: "Категории" },
    { id: "products", label: "Товары" },
    { id: "sets", label: "Наборы" },
    { id: "tags", label: "Теги" },
];

const columnsByTab = {
    categories: [
        { key: "id", label: "ID", width: "64px" },
        { key: "name", label: "Название" },
        { key: "is_active", label: "Активна", width: "100px" },
        { key: "actions", label: "", width: "120px" },
    ],
    products: [
        { key: "id", label: "ID", width: "64px" },
        { key: "name", label: "Название" },
        { key: "price", label: "Цена", width: "90px" },
        { key: "status", label: "Статус", width: "110px" },
        { key: "actions", label: "", width: "120px" },
    ],
    sets: [
        { key: "id", label: "ID", width: "64px" },
        { key: "name", label: "Название" },
        { key: "price", label: "Цена", width: "90px" },
        { key: "status", label: "Статус", width: "110px" },
        { key: "actions", label: "", width: "120px" },
    ],
    tags: [
        { key: "id", label: "ID", width: "64px" },
        { key: "code", label: "Код" },
        { key: "label", label: "Подпись" },
        { key: "is_active", label: "Активен", width: "100px" },
        { key: "actions", label: "", width: "120px" },
    ],
};

const currentColumns = computed(() => columnsByTab[tab.value]);
const currentRows = computed(() => catalog[tab.value] ?? []);
const modalTitle = computed(() => {
    const labels = {
        product: "товар",
        set: "набор",
        category: "категорию",
        tag: "тег",
    };
    const noun = labels[modalKind.value] || "запись";
    return editingId.value ? `Редактировать ${noun}` : `Новый ${noun}`;
});

function clearErrors() {
    Object.keys(fieldErrors).forEach((key) => {
        delete fieldErrors[key];
    });
}

function resetForm() {
    form.name = "";
    form.price = "";
    form.weight = "";
    form.status = "active";
    form.description = "";
    form.sku = "";
    form.is_active = true;
    form.is_accompanying = false;
    form.code = "";
    form.label = "";
    form.color = "#999999";
    clearErrors();
}

async function loadCatalog() {
    try {
        const payload = await catalogApi.run(() => getCatalog());
        catalog.categories = payload?.categories ?? [];
        catalog.products = payload?.products ?? [];
        catalog.sets = payload?.sets ?? [];
        catalog.tags = payload?.tags ?? [];
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить каталог");
    }
}

function openCreate(kind) {
    modalKind.value = kind;
    editingId.value = null;
    resetForm();
    modalOpen.value = true;
}

function openEdit(kind, row) {
    modalKind.value = kind;
    editingId.value = row.id;
    resetForm();
    if (kind === "product") {
        form.name = row.name ?? "";
        form.price = String(row.price ?? "");
        form.weight = row.weight != null ? String(row.weight) : "";
        form.status = row.status ?? "active";
        form.description = row.description ?? "";
        form.sku = row.sku ?? "";
    } else if (kind === "set") {
        form.name = row.name ?? "";
        form.price = String(row.price ?? "");
        form.status = row.status ?? "active";
        form.sku = row.sku ?? "";
    } else if (kind === "category") {
        form.name = row.name ?? "";
        form.is_active = Boolean(row.is_active);
        form.is_accompanying = Boolean(row.is_accompanying);
    } else if (kind === "tag") {
        form.code = row.code ?? "";
        form.label = row.label ?? "";
        form.color = row.color ?? "#999999";
        form.is_active = Boolean(row.is_active);
    }
    modalOpen.value = true;
}

function validate() {
    clearErrors();
    const kind = modalKind.value;
    if (kind === "product" || kind === "set" || kind === "category") {
        if (!String(form.name).trim()) {
            fieldErrors.name = "Обязательное поле";
        }
    }
    if (kind === "product" || kind === "set") {
        if (form.price === "" || Number.isNaN(Number(form.price))) {
            fieldErrors.price = "Укажите цену";
        }
        if (!form.status) {
            fieldErrors.status = "Обязательное поле";
        }
    }
    if (kind === "tag") {
        if (!String(form.code).trim()) {
            fieldErrors.code = "Обязательное поле";
        }
        if (!String(form.label).trim()) {
            fieldErrors.label = "Обязательное поле";
        }
    }
    return Object.keys(fieldErrors).length === 0;
}

async function save() {
    if (!validate()) {
        toast.error("Проверьте обязательные поля");
        return;
    }

    const kind = modalKind.value;
    const id = editingId.value;

    try {
        await saveApi.run(async () => {
            if (kind === "product") {
                const body = {
                    name: form.name.trim(),
                    price: Number(form.price),
                    weight: form.weight === "" ? null : Number(form.weight),
                    status: form.status,
                    description: form.description || null,
                    sku: form.sku || null,
                };
                return id ? updateProduct(id, body) : storeProduct(body);
            }
            if (kind === "set") {
                const body = {
                    name: form.name.trim(),
                    sku: form.sku || null,
                    price: Number(form.price),
                    status: form.status,
                };
                return id ? updateSet(id, body) : storeSet(body);
            }
            if (kind === "category") {
                const body = {
                    name: form.name.trim(),
                    is_active: Boolean(form.is_active),
                    is_accompanying: Boolean(form.is_accompanying),
                };
                return id ? updateCategory(id, body) : storeCategory(body);
            }
            const body = {
                code: form.code.trim(),
                label: form.label.trim(),
                color: form.color || "#999999",
                is_active: Boolean(form.is_active),
            };
            return id ? updateTag(id, body) : storeTag(body);
        });
        toast.success(id ? "Сохранено" : "Создано");
        modalOpen.value = false;
        await loadCatalog();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка сохранения");
    }
}

onMounted(loadCatalog);
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <UiCard title="Каталог">
            <template #actions>
                <UiButton @click="openCreate('product')">+ Товар</UiButton>
                <UiButton @click="openCreate('set')">+ Набор</UiButton>
                <UiButton @click="openCreate('category')">+ Категория</UiButton>
                <UiButton @click="openCreate('tag')">+ Тег</UiButton>
            </template>

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

            <UiTable
                :columns="currentColumns"
                :rows="currentRows"
                :loading="catalogApi.loading"
                empty-text="Пусто"
            >
                <template #cell-status="{ value }">
                    <UiBadge
                        :variant="value === 'active' ? 'success' : 'neutral'"
                        :text="String(value)"
                    />
                </template>
                <template #cell-is_active="{ value }">
                    <UiBadge
                        :variant="value ? 'success' : 'neutral'"
                        :text="value ? 'да' : 'нет'"
                    />
                </template>
                <template #cell-actions="{ row }">
                    <UiButton
                        @click.stop="openEdit(
                            tab === 'categories' ? 'category'
                                : tab === 'products' ? 'product'
                                    : tab === 'sets' ? 'set' : 'tag',
                            row,
                        )"
                    >
                        Редактировать
                    </UiButton>
                </template>
            </UiTable>
        </UiCard>

        <UiModal :open="modalOpen" :title="modalTitle" @close="modalOpen = false">
            <div class="space-y-5">
                <template v-if="modalKind === 'product' || modalKind === 'set' || modalKind === 'category'">
                    <UiFormField label="Название" required :error="fieldErrors.name">
                        <UiInput v-model="form.name" />
                    </UiFormField>
                </template>

                <template v-if="modalKind === 'product' || modalKind === 'set'">
                    <UiFormField label="Цена (₽)" required :error="fieldErrors.price">
                        <UiInput v-model="form.price" type="number" />
                    </UiFormField>
                    <UiFormField label="SKU" :error="fieldErrors.sku">
                        <UiInput v-model="form.sku" />
                    </UiFormField>
                    <UiFormField label="Статус" required :error="fieldErrors.status">
                        <select
                            v-model="form.status"
                            class="mt-1 w-full border border-bo-border bg-bo-field px-4 py-2.5 transition-colors hover:border-bo-border-strong focus:border-bo-accent focus:outline-none"
                        >
                            <option value="active">active</option>
                            <option value="archived">archived</option>
                        </select>
                    </UiFormField>
                </template>

                <template v-if="modalKind === 'product'">
                    <UiFormField label="Вес (г)">
                        <UiInput v-model="form.weight" type="number" />
                    </UiFormField>
                    <UiFormField label="Описание">
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="mt-1 w-full border border-bo-border bg-bo-field px-4 py-2.5 transition-colors hover:border-bo-border-strong focus:border-bo-accent focus:outline-none"
                        />
                    </UiFormField>
                </template>

                <template v-if="modalKind === 'category'">
                    <UiFormField label="Активна">
                        <input v-model="form.is_active" type="checkbox" class="mt-2">
                    </UiFormField>
                    <UiFormField label="Сопутствующая">
                        <input v-model="form.is_accompanying" type="checkbox" class="mt-2">
                    </UiFormField>
                </template>

                <template v-if="modalKind === 'tag'">
                    <UiFormField label="Код" required :error="fieldErrors.code">
                        <UiInput v-model="form.code" />
                    </UiFormField>
                    <UiFormField label="Подпись" required :error="fieldErrors.label">
                        <UiInput v-model="form.label" />
                    </UiFormField>
                    <UiFormField label="Цвет">
                        <UiInput v-model="form.color" />
                    </UiFormField>
                    <UiFormField label="Активен">
                        <input v-model="form.is_active" type="checkbox" class="mt-2">
                    </UiFormField>
                </template>
            </div>

            <template #footer>
                <UiButton @click="modalOpen = false">Отмена</UiButton>
                <UiButton :disabled="saveApi.loading" @click="save">
                    Сохранить
                </UiButton>
            </template>
        </UiModal>
    </div>
</template>
