<script setup>
import { computed, onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import { deleteProduct } from "../../../api/catalog.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useClientList } from "../../../shell/composables/useClientList.js";
import { useToast } from "../../../shell/composables/useToast.js";
import ShellIcon from "../../../shell/components/ShellIcon.vue";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiConfirmDialog from "../../../shell/components/ui/UiConfirmDialog.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";
import { fetchAllProducts } from "../composables/fetchAllProducts.js";

defineProps({
    /** Без внешней рамки страницы — для хаба «Каталог». */
    embedded: { type: Boolean, default: false },
});

const router = useRouter();
const toast = useToast();
const catalogApi = useApi();
const deleteApi = useApi();
const { searchQuery, page, perPage, total, pageItems, setItems } = useClientList({
    perPage: 20,
    getHaystack: (row) => `${row.name ?? ""} ${row.sku ?? ""} ${row.id ?? ""}`,
});

const confirmOpen = ref(false);
/** @type {import('vue').Ref<{ id: number, title: string }|null>} */
const deleteTarget = ref(null);

const columns = [
    { key: "name", label: "Название" },
    { key: "flags", label: "Флаги", width: "160px" },
    { key: "status", label: "Статус", width: "110px" },
    { key: "actions", label: "", width: "112px" },
];

const deleteConfirmMessage = computed(() => {
    const target = deleteTarget.value;
    if (!target) {
        return "";
    }
    return `Удалить «${target.title}»? Это действие нельзя отменить.`;
});

async function load() {
    try {
        const items = await catalogApi.run(() => fetchAllProducts());
        setItems(items);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить каталог");
    }
}

function openCreate() {
    router.push({ name: "owner-catalog-product", params: { id: "new" } });
}

function openEdit(row) {
    router.push({ name: "owner-catalog-product", params: { id: row.id } });
}

function askDelete(row) {
    if (row.is_system) {
        toast.error("Системный товар нельзя удалить");
        return;
    }
    deleteTarget.value = { id: Number(row.id), title: String(row.name || `#${row.id}`) };
    confirmOpen.value = true;
}

async function confirmDelete() {
    const target = deleteTarget.value;
    confirmOpen.value = false;
    deleteTarget.value = null;
    if (!target?.id) {
        return;
    }
    try {
        await deleteApi.run(() => deleteProduct(target.id));
        toast.success("Удалено");
        await load();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось удалить");
    }
}

onMounted(load);
</script>

<template>
    <div
        :class="
            embedded
                ? 'space-y-8'
                : 'mx-auto w-full max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-10 lg:py-10'
        "
    >
        <UiCard title="Товары справочника">
            <template #actions>
                <UiButton @click="openCreate">+ Товар</UiButton>
            </template>

            <div class="mb-4 max-w-md">
                <UiInput
                    v-model="searchQuery"
                    type="search"
                    placeholder="Поиск по названию или артикулу"
                />
            </div>

            <UiTable
                :columns="columns"
                :rows="pageItems"
                :loading="catalogApi.loading"
                empty-text="Ничего не найдено"
            >
                <template #cell-flags="{ row }">
                    <div class="flex flex-wrap gap-1">
                        <UiBadge v-if="row.is_roll" variant="neutral" text="ролл" />
                        <span v-if="!row.is_roll" class="text-bo-muted">—</span>
                    </div>
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
                            :disabled="row.is_system"
                            @click.stop="askDelete(row)"
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
        </UiCard>

        <UiConfirmDialog
            :open="confirmOpen"
            title="Удаление"
            :message="deleteConfirmMessage"
            @confirm="confirmDelete"
            @cancel="
                confirmOpen = false;
                deleteTarget = null;
            "
        />
    </div>
</template>
