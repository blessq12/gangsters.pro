<script setup>
import { onMounted, ref, watch } from "vue";
import { useRouter } from "vue-router";
import { listClients } from "../../../api/clients.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { usePagedList } from "../../../shell/composables/usePagedList.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";

const router = useRouter();
const toast = useToast();
const listApi = useApi();
const { page, perPage, total, items: rows, offset, resetPage, applyPayload } = usePagedList();

const q = ref("");

const columns = [
    { key: "name", label: "Клиент" },
    { key: "phone", label: "Телефон", width: "140px" },
    { key: "email", label: "Email", truncate: true },
    { key: "default_address", label: "Адрес", truncate: true },
    { key: "orders_count", label: "Заказов", width: "88px", align: "right" },
    { key: "last_order_at", label: "Последний заказ", width: "170px" },
    { key: "created_at", label: "Создан", width: "160px" },
];

function formatDate(value) {
    if (!value) {
        return "—";
    }
    try {
        return new Date(value).toLocaleString("ru-RU");
    } catch {
        return String(value);
    }
}

async function load() {
    try {
        const payload = await listApi.run(() =>
            listClients({
                offset: offset.value,
                limit: perPage,
                q: q.value.trim() || undefined,
            }),
        );
        applyPayload(payload, (client) => ({
            id: client.id,
            name: client.name || "—",
            phone: client.phone || "—",
            email: client.email || "—",
            default_address: client.default_address || "—",
            orders_count: Number(client.orders_count ?? 0),
            last_order_at: formatDate(client.last_order_at),
            created_at: formatDate(client.created_at),
            _raw: client,
        }));
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить клиентов");
    }
}

/**
 * @param {{ id: number|string }} row
 */
function openClient(row) {
    router.push({ name: "administrator-client", params: { id: String(row.id) } });
}

let searchTimer = null;
watch(q, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        resetPage();
        load();
    }, 300);
});

watch(page, load);
onMounted(load);
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <UiCard title="Клиенты">
            <div class="mb-6 max-w-md">
                <UiInput v-model="q" placeholder="Поиск: имя / телефон / email" />
            </div>

            <UiTable
                :columns="columns"
                :rows="rows"
                :loading="listApi.loading"
                empty-text="Нет клиентов"
                @row-click="openClient"
            />

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
