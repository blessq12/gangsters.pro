<script setup>
import { computed, onMounted, ref } from "vue";
import { storeToRefs } from "pinia";
import { getDashboardSummary } from "../../../api/dashboard.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useDashboardPeriod } from "../../../shell/composables/useDashboardPeriod.js";
import { useToast } from "../../../shell/composables/useToast.js";
import { useAuthStore } from "../../../shell/store/authStore.js";
import DashboardMetricCard from "../../../shell/components/dashboard/DashboardMetricCard.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";
import { formatKopecks } from "../../../shell/domain/orderStage.js";

const toast = useToast();
const auth = useAuthStore();
const { employee } = storeToRefs(auth);
const summaryApi = useApi();
const { filters, presets, applyPreset, queryParams } = useDashboardPeriod();

const summary = ref(/** @type {Record<string, unknown>|null} */ (null));

const branchId = computed(() => {
    const raw = employee.value?.branch_id;
    const id = Number(raw);
    return Number.isFinite(id) && id > 0 ? id : null;
});

const money = computed(() => summary.value?.money ?? {});
const orders = computed(() => summary.value?.orders ?? {});
const ops = computed(() => summary.value?.ops ?? {});

async function load() {
    if (!branchId.value) {
        summary.value = null;
        return;
    }
    try {
        summary.value = await summaryApi.run(() =>
            getDashboardSummary({
                ...queryParams(),
            }),
        );
    } catch (err) {
        summary.value = null;
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить дашборд");
    }
}

function onFilterCommit() {
    load();
}

onMounted(async () => {
    if (!auth.bootstrapped) {
        await auth.bootstrap();
    }
    applyPreset("today");
    await load();
});
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <h1 class="text-2xl font-semibold tracking-tight text-bo-fg">Дашборд</h1>

        <p v-if="!branchId" class="text-sm text-bo-muted">Нет филиала смены</p>

        <template v-else>
            <UiCard title="Сейчас">
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <DashboardMetricCard
                        label="Новые"
                        :value="Number(ops.new ?? 0)"
                        :to="{ name: 'administrator-live-orders', query: { status: 'new' } }"
                    />
                    <DashboardMetricCard
                        label="Готовится"
                        :value="Number(ops.preparing ?? 0)"
                        :to="{ name: 'administrator-live-orders', query: { status: 'preparing' } }"
                    />
                    <DashboardMetricCard
                        label="Готов"
                        :value="Number(ops.ready ?? 0)"
                        :to="{ name: 'administrator-live-orders', query: { status: 'ready' } }"
                    />
                    <DashboardMetricCard
                        label="В пути"
                        :value="Number(ops.in_transit ?? 0)"
                        :to="{ name: 'administrator-live-orders', query: { status: 'in_transit' } }"
                    />
                    <DashboardMetricCard
                        label="Открытые листы"
                        :value="Number(ops.open_route_sheets ?? 0)"
                        :to="{ name: 'administrator-logistics' }"
                    />
                    <DashboardMetricCard
                        label="Тикеты в очереди"
                        :value="Number(ops.queued_tickets ?? 0)"
                        :to="{ name: 'administrator-production' }"
                    />
                </div>
            </UiCard>

            <div class="flex flex-wrap gap-2">
                <UiButton
                    v-for="preset in presets"
                    :key="preset.key"
                    class="!h-9 !px-3"
                    @click="
                        applyPreset(preset.key);
                        load();
                    "
                >
                    {{ preset.label }}
                </UiButton>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <UiFormField label="С">
                    <UiInput v-model="filters.from" type="date" @change="onFilterCommit" />
                </UiFormField>
                <UiFormField label="По">
                    <UiInput v-model="filters.to" type="date" @change="onFilterCommit" />
                </UiFormField>
            </div>

            <UiCard title="Деньги за период">
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                    <DashboardMetricCard
                        label="Выручка"
                        :value="formatKopecks(money.paid_kopecks)"
                    />
                    <DashboardMetricCard
                        label="Средний чек"
                        :value="formatKopecks(money.average_paid_kopecks)"
                    />
                    <DashboardMetricCard label="Оплачено" :value="Number(money.paid_count ?? 0)" />
                    <DashboardMetricCard
                        label="Ожидает оплаты"
                        :value="formatKopecks(money.pending_kopecks)"
                    />
                    <DashboardMetricCard
                        label="Возвраты"
                        :value="formatKopecks(money.refunded_kopecks)"
                    />
                </div>
            </UiCard>

            <UiCard title="Заказы за период">
                <div class="grid gap-3 sm:grid-cols-3">
                    <DashboardMetricCard
                        label="Создано"
                        :value="Number(orders.created_count ?? 0)"
                        :to="{ name: 'administrator-live-orders' }"
                    />
                    <DashboardMetricCard
                        label="Выдано"
                        :value="Number(orders.delivered_count ?? 0)"
                        :to="{ name: 'administrator-live-orders', query: { status: 'delivered' } }"
                    />
                    <DashboardMetricCard
                        label="Отменено"
                        :value="Number(orders.cancelled_count ?? 0)"
                        :to="{ name: 'administrator-live-orders', query: { status: 'cancelled' } }"
                    />
                </div>
            </UiCard>
        </template>
    </div>
</template>
