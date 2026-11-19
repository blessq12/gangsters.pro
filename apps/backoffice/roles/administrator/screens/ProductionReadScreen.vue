<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { getQueue, getTicket } from "../../../api/production.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { usePagedList } from "../../../shell/composables/usePagedList.js";
import { useKitchenChoice } from "../../../shell/composables/useKitchenChoice.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiModal from "../../../shell/components/ui/UiModal.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";
import {
    orderStatusLabel,
    orderStatusVariant,
    ticketHint,
    ticketStatusLabel,
    ticketStatusVariant,
} from "../../../shell/domain/orderStage.js";

const toast = useToast();
const listApi = useApi();
const detailApi = useApi();
const {
    kitchens,
    kitchenId,
    error: kitchensError,
    load: loadKitchens,
    select: selectKitchen,
    kitchenLabel,
} = useKitchenChoice();

const {
    page,
    perPage,
    total,
    items: rows,
    offset,
    resetPage,
    applyPayload,
    clearItems,
} = usePagedList();
const detailOpen = ref(false);
const detail = ref(null);

const columns = [
    { key: "order_id", label: "Заказ", width: "90px" },
    { key: "status", label: "Тикет", width: "140px" },
    { key: "order_status", label: "Этап заказа", width: "160px" },
    { key: "next", label: "Дальше" },
];

const tableRows = computed(() =>
    rows.value.map((row) => ({
        order_id: row.order_id,
        status: row.status,
        order_status: row.order?.status ?? "",
        next: ticketHint(row.status),
        _raw: row,
    })),
);

async function load() {
    if (!kitchenId.value) {
        clearItems();
        return;
    }
    try {
        const payload = await listApi.run(() => getQueue(kitchenId.value, {
            offset: offset.value,
            limit: perPage,
        }));
        applyPayload(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить тикеты");
    }
}

async function openDetail(row) {
    detail.value = null;
    detailOpen.value = true;
    try {
        detail.value = await detailApi.run(() => getTicket(row.order_id));
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось открыть тикет");
        detailOpen.value = false;
    }
}

watch(kitchenId, () => {
    if (page.value !== 1) {
        resetPage();
        return;
    }
    load();
});
watch(page, load);
onMounted(async () => {
    await loadKitchens();
    await load();
});
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <UiCard title="Тикеты">
            <p class="mb-4 text-sm text-bo-muted">
                Просмотр очереди. Старт и завершение делает кухня, отсюда статус тикета не переключается.
            </p>
            <label class="mb-6 block max-w-md text-sm text-bo-muted" for="admin-kitchen">
                Кухня
                <select
                    id="admin-kitchen"
                    class="mt-1 w-full border border-bo-border bg-bo-field px-4 py-2.5 text-sm text-bo-fg focus:border-bo-accent focus:outline-none"
                    :value="kitchenId"
                    @change="selectKitchen($event.target.value)"
                >
                    <option value="">Выбери кухню</option>
                    <option
                        v-for="kitchen in kitchens"
                        :key="kitchen.id"
                        :value="String(kitchen.id)"
                    >
                        {{ kitchenLabel(kitchen) }}
                    </option>
                </select>
            </label>
            <p v-if="kitchensError" class="mb-4 text-sm text-red-300">
                {{ kitchensError }}
            </p>

            <p v-if="!kitchenId" class="text-sm text-bo-muted">
                Сначала выбери кухню — очередь своя у каждой.
            </p>
            <UiTable
                v-else
                :columns="columns"
                :rows="tableRows"
                :loading="listApi.loading"
                empty-text="На этой кухне нет тикетов в работе"
                row-key="order_id"
                @row-click="openDetail"
            >
                <template #cell-order_id="{ value }">
                    #{{ value }}
                </template>
                <template #cell-status="{ value }">
                    <UiBadge
                        :variant="ticketStatusVariant(value)"
                        :text="ticketStatusLabel(value)"
                    />
                </template>
                <template #cell-order_status="{ value }">
                    <UiBadge
                        v-if="value"
                        :variant="orderStatusVariant(value)"
                        :text="orderStatusLabel(value)"
                    />
                    <span v-else class="text-bo-muted">—</span>
                </template>
            </UiTable>
            <div v-if="kitchenId" class="mt-6">
                <UiPagination
                    :page="page"
                    :per-page="perPage"
                    :total="total"
                    @update:page="page = $event"
                />
            </div>
        </UiCard>

        <UiModal :open="detailOpen" title="Тикет" @close="detailOpen = false">
            <div v-if="detailApi.loading" class="text-sm text-bo-muted">
                Загрузка…
            </div>
            <div v-else-if="detail" class="space-y-3 text-sm">
                <p class="border border-bo-border bg-white/[0.03] px-4 py-3">
                    {{ ticketHint(detail.status) }}
                </p>
                <p>Заказ #{{ detail.order_id }}</p>
                <p>
                    Тикет:
                    <UiBadge
                        class="ml-2"
                        :variant="ticketStatusVariant(detail.status)"
                        :text="ticketStatusLabel(detail.status)"
                    />
                </p>
                <p v-if="detail.order?.status">
                    Заказ: {{ orderStatusLabel(detail.order.status) }}
                </p>
                <ul class="space-y-2">
                    <li
                        v-for="(line, idx) in (detail.lines ?? [])"
                        :key="idx"
                        class="border border-bo-border px-3 py-2"
                    >
                        {{ line.name || `Позиция #${line.product_id}` }} × {{ line.quantity }}
                    </li>
                </ul>
                <p v-if="detail.order?.comment" class="text-bo-muted">
                    Комментарий: {{ detail.order.comment }}
                </p>
            </div>
            <template #footer>
                <UiButton @click="detailOpen = false">
                    Закрыть
                </UiButton>
            </template>
        </UiModal>
    </div>
</template>
