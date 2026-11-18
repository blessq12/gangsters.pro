<script setup>
import { computed, onMounted, ref } from "vue";
import { getCatalog } from "../../../api/catalog.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";

const toast = useToast();
const catalogApi = useApi();

const tab = ref("products");
const catalog = ref({
    products: [],
    sets: [],
    categories: [],
});

const tabs = [
    { id: "products", label: "Товары" },
    { id: "sets", label: "Наборы" },
    { id: "categories", label: "Категории" },
];

const columns = [
    { key: "id", label: "ID", width: "64px" },
    { key: "name", label: "Название" },
    { key: "price", label: "Цена", width: "90px" },
    { key: "is_active", label: "Активен", width: "100px" },
];

const rows = computed(() => {
    const list = catalog.value[tab.value] ?? [];
    return list.map((item) => ({
        id: item.id,
        name: item.name,
        price: item.price != null ? `${item.price} ₽` : "—",
        is_active: item.is_active ?? item.status === "active",
    }));
});

async function load() {
    try {
        const payload = await catalogApi.run(() => getCatalog());
        catalog.value = {
            products: payload?.products ?? [],
            sets: payload?.sets ?? [],
            categories: payload?.categories ?? [],
        };
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить каталог");
    }
}

onMounted(load);
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <UiCard title="Каталог (просмотр)">
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
        </UiCard>
    </div>
</template>
