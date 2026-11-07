<script setup>
import { onMounted, ref, watch } from "vue";
import { storeToRefs } from "pinia";
import Pager from "./Pager.vue";
import {
    deliveryMethodLabel,
    paymentMethodLabel,
    sourceLabel,
    statusColor,
    statusLabel,
    useOpsBoard,
} from "./opsBoard";

const board = useOpsBoard();
const { journalOrders, journalPage, journalTotal, journalReady, journalCounts } = storeToRefs(board);

const statusFilter = ref("");
const openIds = ref(new Set());
const detailTab = ref("overview");
const rejectReason = ref("");
const rejectTargetId = ref(null);

const statusOptions = [
    { id: "", label: "Все" },
    { id: "new", label: statusLabel("new") },
    { id: "preorder", label: statusLabel("preorder") },
    { id: "confirmed", label: statusLabel("confirmed") },
    { id: "preparing", label: statusLabel("preparing") },
    { id: "ready", label: statusLabel("ready") },
    { id: "in_transit", label: statusLabel("in_transit") },
    { id: "cancelled", label: statusLabel("cancelled") },
    { id: "delivered", label: statusLabel("delivered") },
];

const rowColumns = "9rem minmax(0,1.15fr) minmax(0,1.45fr) 9.5rem 9rem 6.5rem 10rem";
const cartColumns = "minmax(0,1fr) 6rem 7rem";

const detailTabs = [
    { id: "overview", label: "Общее" },
    { id: "cart", label: "Корзина" },
    { id: "client", label: "Клиент" },
    { id: "delivery", label: "Доставка" },
    { id: "payment", label: "Оплата" },
];

onMounted(() => {
    board.loadJournal(1, statusFilter.value);
});

watch(statusFilter, (status) => {
    board.loadJournal(1, status);
});

async function reloadJournal() {
    await board.loadJournal(journalPage.value, statusFilter.value);
}

function showPage(page) {
    board.loadJournal(page, statusFilter.value);
}

async function confirm(id) {
    await board.confirmOrder(id);
    await reloadJournal();
}

async function handoff(id) {
    await board.handoffPickup(id);
    await reloadJournal();
}

function isOpen(id) {
    return openIds.value.has(id);
}

function toggle(id) {
    const next = new Set(openIds.value);
    if (next.has(id)) {
        next.delete(id);
    } else {
        next.add(id);
        detailTab.value = "overview";
    }
    openIds.value = next;
}

function statusCount(status) {
    if (status === "") {
        return Object.values(journalCounts.value).reduce((sum, count) => sum + Number(count || 0), 0);
    }
    return Number(journalCounts.value[status] || 0);
}

function statusMark(status) {
    return { borderLeft: `4px solid ${statusColor(status)}` };
}

function statusPillStyle(status, active) {
    const color = statusColor(status);
    if (active) {
        return { backgroundColor: color, color: "#ffffff" };
    }
    return { backgroundColor: "#ffffff", color: "#404040", boxShadow: `inset 0 0 0 1px ${color}` };
}

function dash(value) {
    if (value == null || value === "") {
        return "—";
    }
    return String(value);
}

async function submitReject(id) {
    await board.rejectOrder(id, rejectReason.value);
    await reloadJournal();
    const stillOpen = journalOrders.value.some((order) => order.id === id && order.status !== "cancelled");
    if (!stillOpen) {
        rejectTargetId.value = null;
        rejectReason.value = "";
    }
}
</script>

<template>
    <div class="space-y-3">
        <div class="flex flex-wrap gap-1">
            <button
                v-for="option in statusOptions"
                :key="option.id"
                type="button"
                class="rounded-full px-3 py-1 text-sm"
                :class="option.id === '' && statusFilter === option.id ? 'bg-neutral-900 text-white' : option.id === '' ? 'bg-white text-neutral-700' : ''"
                :style="option.id === '' ? undefined : statusPillStyle(option.id, statusFilter === option.id)"
                @click="statusFilter = option.id"
            >
                {{ option.label }}
                <span class="ml-1">{{ statusCount(option.id) }}</span>
            </button>
        </div>

        <p
            v-if="!journalReady"
            class="text-sm text-neutral-500"
        >
            Загрузка…
        </p>
        <p
            v-else-if="journalOrders.length === 0"
            class="text-sm text-neutral-500"
        >
            Заказы не найдены.
        </p>

        <div
            v-else
            class="space-y-3"
        >
            <div
                class="grid items-end gap-x-8 px-5 text-xs text-neutral-500"
                :style="{ gridTemplateColumns: rowColumns }"
            >
                <span>Заказ</span>
                <span>Клиент</span>
                <span>Получение</span>
                <span>Оплата</span>
                <span>Статус</span>
                <span class="text-right">Сумма</span>
                <span />
            </div>
            <ul class="space-y-3">
            <li
                v-for="order in journalOrders"
                :key="order.id"
                class="rounded border border-neutral-200 bg-white"
                :style="statusMark(order.status)"
            >
                <div
                    class="grid items-center gap-x-8 px-5 py-4"
                    :style="{ gridTemplateColumns: rowColumns }"
                >
                    <button
                        type="button"
                        class="col-span-6 grid grid-cols-subgrid items-center gap-x-8 text-left"
                        :aria-expanded="isOpen(order.id)"
                        @click="toggle(order.id)"
                    >
                        <span class="min-w-0">
                            <span class="block">#{{ order.id }}</span>
                            <span class="mt-1 block text-neutral-500">{{ dash(order.createdAt) }}</span>
                            <span class="mt-1 block text-neutral-500">{{ sourceLabel(order.source ?? "site") }}</span>
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate">{{ order.clientName }}</span>
                            <span class="mt-1 block text-neutral-500">{{ dash(order.phone) }}</span>
                        </span>
                        <span class="min-w-0">
                            <span class="block">{{ deliveryMethodLabel(order.deliveryMethod) }}</span>
                            <span class="mt-1 block truncate text-neutral-500">{{ order.deliveryMethod === "courier" ? dash(order.address) : "—" }}</span>
                        </span>
                        <span class="min-w-0">{{ paymentMethodLabel(order.payment) }}</span>
                        <span>
                            <span
                                class="inline-flex rounded-full px-2 py-0.5 text-white"
                                :style="{ backgroundColor: statusColor(order.status) }"
                            >
                                {{ statusLabel(order.status) }}
                            </span>
                        </span>
                        <span class="text-right">{{ order.total }} ₽</span>
                    </button>
                    <div class="flex flex-col gap-2">
                        <button
                            v-if="order.status === 'new' || order.status === 'preorder'"
                            type="button"
                            class="rounded bg-neutral-900 px-2 py-1 text-white"
                            @click="confirm(order.id)"
                        >
                            Подтвердить
                        </button>
                        <button
                            v-if="order.status === 'new' || order.status === 'preorder'"
                            type="button"
                            class="rounded border border-neutral-300 px-2 py-1"
                            @click="rejectTargetId = order.id"
                        >
                            Отклонить
                        </button>
                        <button
                            v-if="order.status === 'ready' && order.deliveryMethod === 'pickup'"
                            type="button"
                            class="rounded bg-neutral-900 px-2 py-1 text-white"
                            @click="handoff(order.id)"
                        >
                            Выдан
                        </button>
                        <form
                            v-if="rejectTargetId === order.id"
                            class="flex flex-col gap-2"
                            @submit.prevent="submitReject(order.id)"
                        >
                            <input
                                v-model="rejectReason"
                                required
                                type="text"
                                placeholder="Причина"
                                class="rounded border border-neutral-300 px-2 py-1"
                            >
                            <button
                                type="submit"
                                class="rounded bg-red-700 px-2 py-1 text-white"
                            >
                                Списать
                            </button>
                        </form>
                    </div>
                </div>
                <div
                    v-if="isOpen(order.id)"
                    class="border-t border-neutral-100 bg-neutral-50 px-5 py-5"
                >
                    <div class="mb-5 flex flex-wrap gap-2">
                        <button
                            v-for="item in detailTabs"
                            :key="item.id"
                            type="button"
                            class="rounded px-3 py-1.5 text-sm"
                            :class="detailTab === item.id ? 'bg-neutral-900 text-white' : 'bg-white text-neutral-700'"
                            @click="detailTab = item.id"
                        >
                            {{ item.label }}
                        </button>
                    </div>
                    <dl
                        v-if="detailTab === 'overview'"
                        class="grid grid-cols-2 gap-x-8 gap-y-5 lg:grid-cols-4"
                    >
                        <div>
                            <dt class="text-xs text-neutral-500">Идентификатор</dt>
                            <dd class="mt-1">#{{ order.id }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-500">Источник</dt>
                            <dd class="mt-1">{{ sourceLabel(order.source ?? "site") }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-500">Оформление</dt>
                            <dd class="mt-1">{{ dash(order.checkoutId) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-500">Партнёр</dt>
                            <dd class="mt-1">{{ dash(order.partnerCode) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-500">Внешний ID заказа</dt>
                            <dd class="mt-1">{{ dash(order.externalOrderId) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-500">Статус</dt>
                            <dd class="mt-1">
                                <span
                                    class="inline-flex rounded-full px-2 py-0.5 text-white"
                                    :style="{ backgroundColor: statusColor(order.status) }"
                                >
                                    {{ statusLabel(order.status) }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-500">Сумма товаров</dt>
                            <dd class="mt-1">{{ order.total }} ₽</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-500">Создан</dt>
                            <dd class="mt-1">{{ dash(order.createdAt) }}</dd>
                        </div>
                    </dl>
                    <div
                        v-else-if="detailTab === 'cart'"
                        class="space-y-3"
                    >
                        <div
                            class="grid gap-x-8 text-xs text-neutral-500"
                            :style="{ gridTemplateColumns: cartColumns }"
                        >
                            <span>Название</span>
                            <span>Кол-во</span>
                            <span class="text-right">Сумма</span>
                        </div>
                        <div
                            v-for="line in order.lines"
                            :key="line.name"
                            class="grid items-center gap-x-8 rounded border border-neutral-200 bg-white px-4 py-3"
                            :style="{ gridTemplateColumns: cartColumns }"
                        >
                            <span class="min-w-0 truncate">{{ line.name }}</span>
                            <span>{{ line.qty }}</span>
                            <span class="text-right">{{ line.total ?? "—" }}</span>
                        </div>
                    </div>
                    <dl
                        v-else-if="detailTab === 'client'"
                        class="grid grid-cols-2 gap-x-8 gap-y-5 lg:grid-cols-4"
                    >
                        <div>
                            <dt class="text-xs text-neutral-500">Тип клиента</dt>
                            <dd class="mt-1">{{ order.clientKind === "registered" ? "Авторизованный клиент" : "Гость" }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-500">ID клиента</dt>
                            <dd class="mt-1">{{ dash(order.clientId) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-500">Имя</dt>
                            <dd class="mt-1">{{ order.clientName }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-500">Телефон</dt>
                            <dd class="mt-1">{{ dash(order.phone) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-500">Email</dt>
                            <dd class="mt-1">{{ dash(order.email) }}</dd>
                        </div>
                    </dl>
                    <dl
                        v-else-if="detailTab === 'delivery'"
                        class="grid grid-cols-2 gap-x-8 gap-y-5 lg:grid-cols-4"
                    >
                        <div>
                            <dt class="text-xs text-neutral-500">Способ доставки</dt>
                            <dd class="mt-1">{{ deliveryMethodLabel(order.deliveryMethod) }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-xs text-neutral-500">Адрес</dt>
                            <dd class="mt-1">{{ dash(order.address) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-500">Время доставки</dt>
                            <dd class="mt-1">{{ dash(order.scheduledAt) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-500">Персон</dt>
                            <dd class="mt-1">{{ dash(order.persons) }}</dd>
                        </div>
                        <div class="col-span-2 lg:col-span-3">
                            <dt class="text-xs text-neutral-500">Комментарий</dt>
                            <dd class="mt-1">{{ dash(order.comment) }}</dd>
                        </div>
                    </dl>
                    <dl
                        v-else
                        class="grid grid-cols-2 gap-x-8 gap-y-5 lg:grid-cols-4"
                    >
                        <div>
                            <dt class="text-xs text-neutral-500">Способ оплаты</dt>
                            <dd class="mt-1">{{ paymentMethodLabel(order.payment) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-500">Сдача с</dt>
                            <dd class="mt-1">{{ dash(order.changeFrom) }}</dd>
                        </div>
                    </dl>
                </div>
            </li>
            </ul>
        </div>
        <Pager
            :page="journalPage"
            :total="journalTotal"
            :per-page="board.journalPerPage"
            @update:page="showPage"
        />
    </div>
</template>
