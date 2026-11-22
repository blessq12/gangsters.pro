<script setup>
import { computed, onMounted, watch } from "vue";
import { listProducts } from "../../../api/catalog.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { usePagedList } from "../../../shell/composables/usePagedList.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";

const toast = useToast();
const catalogApi = useApi();
const {
    page,
    perPage,
    total,
    items,
    offset,
    applyPayload,
} = usePagedList();

const columns = [
    { key: "name", label: "Название" },
    { key: "is_active", label: "Активен", width: "100px" },
];

const rows = computed(() => items.value.map((item) => ({
    id: item.id,
    name: item.name,
    is_active: item.is_active ?? item.status === "active",
})));

async function load() {
    try {
        const payload = await catalogApi.run(() => listProducts({
            offset: offset.value,
            limit: perPage,
        }));
        applyPayload(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить каталог");
    }
}

watch(page, load);
onMounted(load);
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <UiCard title="Каталог (просмотр)">
            <UiTable
                :columns="columns"
                :rows="rows"
                :loading="catalogApi.loading"
                empty-text="Пусто"
            >
                <template #cell-is_active="{ value }">
                    <UiBadge
                        :variant="value ? 'success' : 'neutral'"
                        :text="value ? 'да' : 'нет'"
                    />
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
    </div>
</template>
