<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import {
    deleteBranchSet,
    listBranchSets,
    listBranchTags,
    storeBranchSet,
    updateBranchSet,
} from "../../../../api/company.js";
import { useApi } from "../../../../shell/composables/useApi.js";
import { useClientList } from "../../../../shell/composables/useClientList.js";
import { useToast } from "../../../../shell/composables/useToast.js";
import ShellIcon from "../../../../shell/components/ShellIcon.vue";
import UiBadge from "../../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../../shell/components/ui/UiCard.vue";
import UiConfirmDialog from "../../../../shell/components/ui/UiConfirmDialog.vue";
import UiFormField from "../../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../../shell/components/ui/UiInput.vue";
import UiModal from "../../../../shell/components/ui/UiModal.vue";
import UiPagination from "../../../../shell/components/ui/UiPagination.vue";
import UiTable from "../../../../shell/components/ui/UiTable.vue";
import UiToggle from "../../../../shell/components/ui/UiToggle.vue";
import { fetchAllProducts } from "../../composables/fetchAllProducts.js";
import { useOwnerBranchContext } from "../../composables/useOwnerBranchContext.js";
import OwnerBranchSelector from "../branch/OwnerBranchSelector.vue";

defineProps({
    /** Селектор филиала уже в хабе. */
    embedded: { type: Boolean, default: false },
});

const toast = useToast();
const loadApi = useApi();
const saveApi = useApi();
const { selectedBranchId, ensureLoaded } = useOwnerBranchContext();

const {
    searchQuery,
    page,
    perPage,
    total,
    pageItems,
    setItems,
} = useClientList({
    perPage: 20,
    getHaystack: (row) => `${row.name ?? ""} ${row.slug ?? ""}`,
});

const products = ref([]);
const tags = ref([]);

const modalOpen = ref(false);
const editingId = ref(null);
const deleteTarget = ref(null);
const addProductId = ref("");
const fieldErrors = reactive({});
const form = reactive({
    name: "",
    slug: "",
    description: "",
    priceRubles: "",
    is_available: true,
    isActive: true,
    /** @type {Array<{ catalog_item_id: number, quantity: string }>} */
    lines: [],
    /** @type {number[]} */
    tag_ids: [],
});

const columns = [
    { key: "name", label: "Название" },
    { key: "price_rubles", label: "Цена", width: "110px" },
    { key: "lines", label: "Состав", width: "100px" },
    { key: "is_available", label: "В продаже", width: "110px" },
    { key: "status", label: "Статус", width: "110px" },
    { key: "actions", label: "", width: "112px" },
];

const modalTitle = computed(() => (editingId.value ? "Редактировать набор" : "Новый набор"));
const confirmMessage = computed(() =>
    deleteTarget.value ? `Удалить набор «${deleteTarget.value.name}»?` : "",
);

const productNameById = computed(
    () => new Map(products.value.map((item) => [Number(item.id), item.name])),
);

const availableProducts = computed(() => {
    const used = new Set(form.lines.map((line) => line.catalog_item_id));
    return products.value.filter((item) => !used.has(Number(item.id)));
});

function productName(id) {
    return productNameById.value.get(Number(id)) ?? `Товар #${id}`;
}

function clearErrors() {
    Object.keys(fieldErrors).forEach((key) => {
        delete fieldErrors[key];
    });
}

/** Рубли из поля ввода → копейки; null, если значение некорректно. */
function rublesToKopecks(value) {
    const text = String(value ?? "").trim().replace(",", ".");
    if (text === "" || Number.isNaN(Number(text)) || Number(text) < 0) {
        return null;
    }
    return Math.round(Number(text) * 100);
}

async function load() {
    const branchId = selectedBranchId.value;
    if (!branchId) {
        setItems([]);
        return;
    }
    try {
        const [setsPayload, tagsPayload, allProducts] = await loadApi.run(() => Promise.all([
            listBranchSets(branchId),
            listBranchTags(branchId),
            fetchAllProducts(),
        ]));
        if (branchId !== selectedBranchId.value) {
            return;
        }
        setItems(Array.isArray(setsPayload?.items) ? setsPayload.items : []);
        tags.value = Array.isArray(tagsPayload?.items) ? tagsPayload.items : [];
        products.value = allProducts;
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить наборы");
    }
}

function openCreate() {
    editingId.value = null;
    clearErrors();
    addProductId.value = "";
    form.name = "";
    form.slug = "";
    form.description = "";
    form.priceRubles = "";
    form.is_available = true;
    form.isActive = true;
    form.lines = [];
    form.tag_ids = [];
    modalOpen.value = true;
}

function openEdit(row) {
    editingId.value = row.id;
    clearErrors();
    addProductId.value = "";
    form.name = row.name ?? "";
    form.slug = row.slug ?? "";
    form.description = row.description ?? "";
    form.priceRubles = String(Number(row.price_kopecks ?? 0) / 100);
    form.is_available = Boolean(row.is_available);
    form.isActive = row.status === "active";
    form.lines = (row.lines ?? []).map((line) => ({
        catalog_item_id: Number(line.catalog_item_id),
        quantity: String(line.quantity),
    }));
    form.tag_ids = Array.isArray(row.tag_ids) ? row.tag_ids.map(Number) : [];
    modalOpen.value = true;
}

function addLine() {
    const id = Number(addProductId.value);
    if (!id) {
        return;
    }
    form.lines.push({ catalog_item_id: id, quantity: "1" });
    addProductId.value = "";
}

function removeLine(index) {
    form.lines.splice(index, 1);
}

function toggleTag(id) {
    const num = Number(id);
    form.tag_ids = form.tag_ids.includes(num)
        ? form.tag_ids.filter((value) => value !== num)
        : [...form.tag_ids, num];
}

function validate() {
    clearErrors();
    if (!String(form.name).trim()) {
        fieldErrors.name = "Обязательное поле";
    }
    if (rublesToKopecks(form.priceRubles) === null) {
        fieldErrors.price = "Укажи цену ≥ 0";
    }
    if (form.lines.length === 0) {
        fieldErrors.lines = "Добавь хотя бы один товар";
    } else if (form.lines.some((line) => !Number.isInteger(Number(line.quantity)) || Number(line.quantity) < 1)) {
        fieldErrors.lines = "Количество — целое число ≥ 1";
    }
    return Object.keys(fieldErrors).length === 0;
}

async function save() {
    const branchId = selectedBranchId.value;
    if (!branchId) {
        toast.error("Выбери филиал");
        return;
    }
    if (!validate()) {
        toast.error("Проверьте поля");
        return;
    }
    const id = editingId.value;
    const body = {
        name: form.name.trim(),
        slug: form.slug.trim() || null,
        description: form.description.trim() || null,
        is_available: Boolean(form.is_available),
        price_kopecks: rublesToKopecks(form.priceRubles),
        status: form.isActive ? "active" : "archived",
        lines: form.lines.map((line) => ({
            catalog_item_id: line.catalog_item_id,
            quantity: Number(line.quantity),
        })),
        tag_ids: form.tag_ids,
    };
    try {
        await saveApi.run(() => (
            id ? updateBranchSet(branchId, id, body) : storeBranchSet(branchId, body)
        ));
        toast.success(id ? "Сохранено" : "Создано");
        modalOpen.value = false;
        await load();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка сохранения");
    }
}

async function confirmDelete() {
    const target = deleteTarget.value;
    const branchId = selectedBranchId.value;
    deleteTarget.value = null;
    if (!target || !branchId) {
        return;
    }
    try {
        await deleteBranchSet(branchId, target.id);
        toast.success("Удалено");
        await load();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось удалить");
    }
}

watch(selectedBranchId, load);
onMounted(async () => {
    await ensureLoaded();
    await load();
});
</script>

<template>
    <UiCard title="Наборы филиала">
        <template #actions>
            <div class="flex flex-wrap items-center gap-2">
                <OwnerBranchSelector v-if="!embedded" />
                <UiButton :disabled="!selectedBranchId" @click="openCreate">
                    + Набор
                </UiButton>
            </div>
        </template>

        <template v-if="selectedBranchId">
            <div class="mb-4 max-w-md">
                <UiInput
                    v-model="searchQuery"
                    type="search"
                    placeholder="Поиск по названию"
                />
            </div>

            <UiTable
                :columns="columns"
                :rows="pageItems"
                :loading="loadApi.loading"
                empty-text="Ничего не найдено"
                @row-click="openEdit"
            >
                <template #cell-price_rubles="{ value }">
                    {{ value }} ₽
                </template>
                <template #cell-lines="{ value }">
                    {{ value.length }} поз.
                </template>
                <template #cell-is_available="{ value }">
                    <UiBadge
                        :variant="value ? 'success' : 'neutral'"
                        :text="value ? 'да' : 'нет'"
                    />
                </template>
                <template #cell-status="{ value }">
                    <UiBadge
                        :variant="value === 'active' ? 'success' : 'neutral'"
                        :text="String(value)"
                    />
                </template>
                <template #cell-actions="{ row }">
                    <div class="flex flex-wrap gap-1">
                        <UiButton
                            class="!h-9 !px-2.5"
                            title="Редактировать"
                            aria-label="Редактировать"
                            @click.stop="openEdit(row)"
                        >
                            <ShellIcon name="edit" class="h-4 w-4" />
                        </UiButton>
                        <UiButton
                            class="!h-9 !px-2.5"
                            title="Удалить"
                            aria-label="Удалить"
                            @click.stop="deleteTarget = row"
                        >
                            <ShellIcon name="trash" class="h-4 w-4" />
                        </UiButton>
                    </div>
                </template>
            </UiTable>

            <div class="mt-6">
                <UiPagination
                    :page="page"
                    :per-page="perPage"
                    :total="total"
                    @update:page="page = $event"
                />
            </div>
        </template>

        <UiModal :open="modalOpen" :title="modalTitle" @close="modalOpen = false">
            <div class="space-y-5">
                <UiFormField label="Название" required :error="fieldErrors.name">
                    <UiInput v-model="form.name" />
                </UiFormField>
                <UiFormField label="Slug (необязательно)">
                    <UiInput v-model="form.slug" />
                </UiFormField>
                <UiFormField label="Описание">
                    <textarea
                        v-model="form.description"
                        rows="3"
                        class="mt-1 w-full border border-bo-border bg-bo-field px-3 py-2 text-sm"
                    />
                </UiFormField>
                <UiFormField label="Цена (₽)" required :error="fieldErrors.price">
                    <UiInput v-model="form.priceRubles" type="number" />
                </UiFormField>
                <div class="flex flex-wrap gap-3">
                    <UiToggle v-model="form.is_available" label-on="В продаже" label-off="Скрыт" />
                    <UiToggle v-model="form.isActive" label-on="Активен" label-off="В архиве" />
                </div>

                <UiFormField label="Состав" required :error="fieldErrors.lines">
                    <ul
                        v-if="form.lines.length > 0"
                        class="mb-3 divide-y divide-bo-border border border-bo-border"
                    >
                        <li
                            v-for="(line, index) in form.lines"
                            :key="line.catalog_item_id"
                            class="flex items-center gap-2 px-3 py-2 text-sm"
                        >
                            <span class="min-w-0 flex-1 truncate">
                                {{ productName(line.catalog_item_id) }}
                            </span>
                            <div class="w-24">
                                <UiInput v-model="line.quantity" type="number" />
                            </div>
                            <UiButton class="!h-8 !px-2" aria-label="Убрать" @click="removeLine(index)">
                                ×
                            </UiButton>
                        </li>
                    </ul>
                    <div class="flex gap-2">
                        <select
                            v-model="addProductId"
                            class="min-w-0 flex-1 border border-bo-border bg-bo-field px-4 py-2.5 text-sm focus:border-bo-accent focus:outline-none"
                        >
                            <option value="">Добавить товар…</option>
                            <option
                                v-for="item in availableProducts"
                                :key="item.id"
                                :value="String(item.id)"
                            >
                                {{ item.name }}
                            </option>
                        </select>
                        <UiButton :disabled="!addProductId" @click="addLine">
                            Добавить
                        </UiButton>
                    </div>
                </UiFormField>

                <UiFormField label="Теги">
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="tag in tags"
                            :key="tag.id"
                            type="button"
                            class="h-8 px-2.5 text-sm transition-colors"
                            :class="form.tag_ids.includes(tag.id)
                                ? 'bg-bo-accent text-white'
                                : 'bg-white/[0.04] text-bo-muted hover:bg-white/[0.08]'"
                            @click="toggleTag(tag.id)"
                        >
                            {{ tag.label || tag.code }}
                        </button>
                        <p v-if="tags.length === 0" class="text-sm text-bo-muted">
                            Тегов в филиале пока нет
                        </p>
                    </div>
                </UiFormField>
            </div>

            <template #footer>
                <UiButton class="!bg-white/10 !text-bo-fg" @click="modalOpen = false">
                    Отмена
                </UiButton>
                <UiButton :disabled="saveApi.loading" @click="save">
                    Сохранить
                </UiButton>
            </template>
        </UiModal>

        <UiConfirmDialog
            :open="deleteTarget !== null"
            title="Удаление набора"
            :message="confirmMessage"
            @confirm="confirmDelete"
            @cancel="deleteTarget = null"
        />
    </UiCard>
</template>
