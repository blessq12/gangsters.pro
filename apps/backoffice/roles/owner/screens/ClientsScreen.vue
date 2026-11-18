<script setup>
import { onMounted, ref, watch } from "vue";
import { getClient, listClients } from "../../../api/clients.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";
import UiModal from "../../../shell/components/ui/UiModal.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";

const toast = useToast();
const listApi = useApi();
const detailApi = useApi();

const page = ref(1);
const perPage = 20;
const total = ref(0);
const q = ref("");
const rows = ref([]);

const detailOpen = ref(false);
const detailClient = ref(null);
const detailOrders = ref([]);

const columns = [
    { key: "id", label: "ID", width: "64px" },
    { key: "name", label: "Имя" },
    { key: "phone", label: "Телефон", width: "140px" },
    { key: "email", label: "Email" },
    { key: "created_at", label: "Создан", width: "160px" },
];

const orderColumns = [
    { key: "id", label: "ID", width: "64px" },
    { key: "status", label: "Статус", width: "120px" },
    { key: "total", label: "Сумма", width: "90px" },
    { key: "created_at", label: "Создан" },
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
        const payload = await listApi.run(() => listClients({
            offset: (page.value - 1) * perPage,
            limit: perPage,
            q: q.value.trim() || undefined,
        }));
        total.value = Number(payload?.total ?? 0);
        rows.value = (payload?.items ?? []).map((client) => ({
            ...client,
            created_at: formatDate(client.created_at),
            _raw: client,
        }));
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить клиентов");
    }
}

async function openDetail(row) {
    detailClient.value = null;
    detailOrders.value = [];
    detailOpen.value = true;
    try {
        const payload = await detailApi.run(() => getClient(row.id));
        detailClient.value = payload?.client ?? null;
        detailOrders.value = (payload?.orders ?? []).map((order) => ({
            id: order.id,
            status: order.status,
            total: `${order.total ?? 0} ₽`,
            created_at: formatDate(order.created_at),
        }));
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить клиента");
        detailOpen.value = false;
    }
}

let searchTimer = null;
watch(q, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        page.value = 1;
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
                <UiInput
                    v-model="q"
                    placeholder="Поиск: имя / телефон / email"
                />
            </div>

            <UiTable
                :columns="columns"
                :rows="rows"
                :loading="listApi.loading"
                empty-text="Нет клиентов"
                @row-click="openDetail"
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

        <UiModal
            :open="detailOpen"
            title="Клиент"
            @close="detailOpen = false"
        >
            <div v-if="detailApi.loading" class="text-sm text-bo-muted">
                Загрузка…
            </div>
            <div v-else-if="detailClient" class="space-y-4 text-sm">
                <div class="space-y-1">
                    <p><span class="text-bo-muted">ID:</span> {{ detailClient.id }}</p>
                    <p><span class="text-bo-muted">Имя:</span> {{ detailClient.name }}</p>
                    <p><span class="text-bo-muted">Телефон:</span> {{ detailClient.phone }}</p>
                    <p><span class="text-bo-muted">Email:</span> {{ detailClient.email ?? "—" }}</p>
                    <p>
                        <span class="text-bo-muted">Создан:</span>
                        {{ formatDate(detailClient.created_at) }}
                    </p>
                </div>

                <div>
                    <p class="mb-2 font-medium">История заказов</p>
                    <UiTable
                        :columns="orderColumns"
                        :rows="detailOrders"
                        empty-text="Нет заказов"
                    >
                        <template #cell-status="{ value }">
                            <UiBadge variant="neutral" :text="String(value)" />
                        </template>
                    </UiTable>
                </div>
            </div>
            <template #footer>
                <UiButton @click="detailOpen = false">Закрыть</UiButton>
            </template>
        </UiModal>
    </div>
</template>
