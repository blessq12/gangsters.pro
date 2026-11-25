<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { getClient } from "../../../api/clients.js";
import { getOrder } from "../../../api/orders.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";
import {
    formatAddress,
    isClosedOrder,
    orderStatusLabel,
    orderStatusVariant,
} from "../../../shell/domain/orderStage.js";
import { useOrderWorkspaceStore } from "../../../shell/store/orderWorkspaceStore.js";
import ShellIcon from "../../../shell/components/ShellIcon.vue";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";

const route = useRoute();
const router = useRouter();
const toast = useToast();
const workspace = useOrderWorkspaceStore();
const loadApi = useApi();
const repeatApi = useApi();

const clientId = computed(() => Number(route.params.id));
const client = ref(/** @type {Record<string, unknown>|null} */ (null));
const orders = ref(/** @type {Array<Record<string, unknown>>} */ ([]));

const title = computed(() => {
    const name = String(client.value?.name ?? "").trim();
    if (name !== "") {
        return name;
    }
    const phone = String(client.value?.phone ?? "").trim();
    return phone !== "" ? phone : `Клиент #${clientId.value}`;
});

const addresses = computed(() =>
    Array.isArray(client.value?.addresses) ? client.value.addresses : [],
);

const orderColumns = [
    { key: "number", label: "Заказ", width: "140px" },
    { key: "status", label: "Статус", width: "130px" },
    { key: "total", label: "Сумма", width: "100px", align: "right" },
    { key: "created_at", label: "Создан", width: "170px" },
    { key: "actions", label: "", width: "56px", align: "center" },
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

function formatAddressLabel(address) {
    const formatted = formatAddress(address);
    if (formatted) {
        return formatted;
    }
    return address?.title || address?.id || "Адрес";
}

async function load() {
    if (!clientId.value) {
        router.push({ name: "administrator-clients" });
        return;
    }
    try {
        const payload = await loadApi.run(() => getClient(clientId.value));
        client.value = payload?.client ?? null;
        orders.value = (Array.isArray(payload?.orders) ? payload.orders : []).map((order) => ({
            id: order.id,
            number: order.number || `ORD-${String(order.id).padStart(4, "0")}`,
            status: order.status,
            total: `${order.total ?? 0} ₽`,
            created_at: formatDate(order.created_at),
            _raw: order,
        }));
        if (!client.value) {
            toast.error("Клиент не найден");
            router.push({ name: "administrator-clients" });
        }
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить клиента");
        router.push({ name: "administrator-clients" });
    }
}

function goBack() {
    router.push({ name: "administrator-clients" });
}

/**
 * @param {{ id: number|string }} row
 */
function openOrder(row) {
    if (!row?.id) {
        return;
    }
    router.push({ name: "administrator-order", params: { id: String(row.id) } });
}

/**
 * @param {MouseEvent} event
 * @param {{ id: number|string }} row
 */
async function repeatOrder(event, row) {
    event.stopPropagation();
    if (!row?.id || !isClosedOrder(row.status)) {
        return;
    }
    try {
        const order = await repeatApi.run(() => getOrder(row.id));
        workspace.hydrateRepeat(order);
        router.push({ name: "administrator-order-new" });
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось подготовить повтор");
    }
}

async function copyPhone() {
    const phone = String(client.value?.phone ?? "").trim();
    if (!phone) {
        toast.error("Телефон не указан");
        return;
    }
    try {
        await navigator.clipboard.writeText(phone);
        toast.success("Телефон скопирован");
    } catch {
        toast.error("Не удалось скопировать");
    }
}

watch(() => route.params.id, load);
onMounted(load);
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-4 px-4 py-5 sm:px-6 lg:px-10 lg:py-7">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
                <UiButton
                    class="!bg-white/10 !text-bo-fg"
                    @click="goBack"
                >
                    К клиентам
                </UiButton>
                <h1 class="text-lg font-semibold tracking-tight text-bo-fg">
                    {{ title }}
                </h1>
            </div>
            <UiButton
                class="!bg-white/10 !text-bo-fg"
                :disabled="!client?.phone"
                @click="copyPhone"
            >
                Копировать телефон
            </UiButton>
        </div>

        <p
            v-if="loadApi.loading"
            class="text-sm text-bo-muted"
        >
            Загрузка…
        </p>

        <template v-else-if="client">
            <div class="grid gap-4 lg:grid-cols-3">
                <div class="space-y-4 lg:col-span-1">
                    <UiCard title="Профиль">
                        <dl class="space-y-2 text-sm">
                            <div>
                                <dt class="text-bo-muted">
                                    Имя
                                </dt>
                                <dd class="text-bo-fg">
                                    {{ client.name || "—" }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-bo-muted">
                                    Телефон
                                </dt>
                                <dd class="text-bo-fg">
                                    {{ client.phone || "—" }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-bo-muted">
                                    Email
                                </dt>
                                <dd class="text-bo-fg">
                                    {{ client.email || "—" }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-bo-muted">
                                    Дата рождения
                                </dt>
                                <dd class="text-bo-fg">
                                    {{ client.birth_date || "—" }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-bo-muted">
                                    Создан
                                </dt>
                                <dd class="text-bo-fg">
                                    {{ formatDate(client.created_at) }}
                                </dd>
                            </div>
                        </dl>
                    </UiCard>

                    <UiCard title="Адреса">
                        <ul
                            v-if="addresses.length"
                            class="space-y-2 text-sm"
                        >
                            <li
                                v-for="address in addresses"
                                :key="address.id"
                                class="border border-bo-border bg-black/20 px-3 py-2"
                            >
                                <p class="text-bo-fg">
                                    {{ formatAddressLabel(address) }}
                                </p>
                                <p
                                    v-if="address.is_default"
                                    class="mt-1 text-xs text-emerald-300"
                                >
                                    основной
                                </p>
                                <p
                                    v-if="address.comment"
                                    class="mt-1 text-xs text-bo-muted"
                                >
                                    {{ address.comment }}
                                </p>
                            </li>
                        </ul>
                        <p
                            v-else
                            class="text-sm text-bo-muted"
                        >
                            Адресов нет
                        </p>
                    </UiCard>
                </div>

                <div class="lg:col-span-2">
                    <UiCard title="История заказов">
                        <UiTable
                            :columns="orderColumns"
                            :rows="orders"
                            empty-text="Нет заказов"
                            @row-click="openOrder"
                        >
                            <template #cell-status="{ value }">
                                <UiBadge
                                    :variant="orderStatusVariant(value)"
                                    :text="orderStatusLabel(value)"
                                />
                            </template>
                            <template #cell-actions="{ row }">
                                <button
                                    v-if="isClosedOrder(row.status)"
                                    type="button"
                                    class="inline-flex h-9 w-9 items-center justify-center border border-bo-border bg-white/10 text-bo-fg transition-colors hover:bg-white/[0.08] disabled:opacity-40"
                                    :disabled="repeatApi.loading"
                                    aria-label="Повторить заказ"
                                    title="Повторить заказ"
                                    @click="repeatOrder($event, row)"
                                >
                                    <ShellIcon
                                        name="undo"
                                        class="h-4 w-4"
                                    />
                                </button>
                                <span
                                    v-else
                                    class="text-bo-muted"
                                >—</span>
                            </template>
                        </UiTable>
                    </UiCard>
                </div>
            </div>
        </template>
    </div>
</template>
