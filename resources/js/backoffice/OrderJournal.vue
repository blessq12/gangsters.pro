<script setup>
import { onMounted, ref, watch } from "vue";
import { storeToRefs } from "pinia";
import Pager from "./Pager.vue";
import {
    deliveryMethodLabel,
    paymentMethodLabel,
    sourceLabel,
    statusLabel,
    useOpsBoard,
} from "./opsBoard";

defineProps({
    readOnly: { type: Boolean, default: false },
});

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
                class="inline-flex h-9 items-center rounded px-3 text-sm"
                :class="statusFilter === option.id ? 'bg-neutral-900 text-white' : 'bg-white text-neutral-700'"
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
            class="overflow-x-auto rounded border border-neutral-200 bg-white"
        >
            <table class="w-full min-w-[980px] text-left text-sm">
                <thead class="border-b border-neutral-200 bg-neutral-50 text-xs text-neutral-500">
                    <tr>
                        <th class="px-3 py-2 font-medium">ID</th>
                        <th class="px-3 py-2 font-medium">Источник</th>
                        <th class="px-3 py-2 font-medium">Статус</th>
                        <th class="px-3 py-2 font-medium">Сумма, ₽</th>
                        <th class="px-3 py-2 font-medium">Клиент</th>
                        <th class="px-3 py-2 font-medium">Доставка</th>
                        <th class="px-3 py-2 font-medium">Оплата</th>
                        <th
                            v-if="readOnly"
                            class="px-3 py-2 font-medium"
                        >
                            Оформление
                        </th>
                        <th
                            v-if="readOnly"
                            class="px-3 py-2 font-medium"
                        >
                            Партнёр
                        </th>
                        <th
                            v-if="readOnly"
                            class="px-3 py-2 font-medium"
                        >
                            Внешний ID
                        </th>
                        <th class="px-3 py-2 font-medium">Создан</th>
                        <th
                            v-if="!readOnly"
                            class="px-3 py-2 font-medium"
                        />
                    </tr>
                </thead>
                <tbody>
                    <template
                        v-for="order in journalOrders"
                        :key="order.id"
                    >
                        <tr
                            class="cursor-pointer border-b border-neutral-100 align-top hover:bg-neutral-50"
                            :class="isOpen(order.id) ? 'bg-neutral-50' : ''"
                            @click="toggle(order.id)"
                        >
                            <td class="px-3 py-2">
                                <button
                                    type="button"
                                    class="mr-2 text-neutral-500"
                                    :aria-expanded="isOpen(order.id)"
                                    @click.stop="toggle(order.id)"
                                >
                                    {{ isOpen(order.id) ? "▾" : "▸" }}
                                </button>
                                {{ order.id }}
                            </td>
                            <td class="px-3 py-2">{{ sourceLabel(order.source ?? "site") }}</td>
                            <td class="px-3 py-2">{{ statusLabel(order.status) }}</td>
                            <td class="px-3 py-2">{{ order.total }}</td>
                            <td class="px-3 py-2">
                                {{ order.clientName }}
                                <span class="text-neutral-500">{{ dash(order.phone) }}</span>
                            </td>
                            <td class="px-3 py-2">{{ deliveryMethodLabel(order.deliveryMethod) }}</td>
                            <td class="px-3 py-2">{{ paymentMethodLabel(order.payment) }}</td>
                            <td
                                v-if="readOnly"
                                class="px-3 py-2"
                            >
                                {{ dash(order.checkoutId) }}
                            </td>
                            <td
                                v-if="readOnly"
                                class="px-3 py-2"
                            >
                                {{ dash(order.partnerCode) }}
                            </td>
                            <td
                                v-if="readOnly"
                                class="px-3 py-2"
                            >
                                {{ dash(order.externalOrderId) }}
                            </td>
                            <td class="px-3 py-2">{{ dash(order.createdAt) }}</td>
                            <td
                                v-if="!readOnly"
                                class="px-3 py-2"
                                @click.stop
                            >
                                <div class="flex flex-wrap gap-2">
                        <button
                            v-if="order.status === 'new' || order.status === 'preorder'"
                            type="button"
                            class="inline-flex h-9 items-center rounded bg-neutral-900 px-3 text-sm text-white"
                            @click="confirm(order.id)"
                        >
                            Подтвердить
                        </button>
                        <button
                            v-if="order.status === 'new' || order.status === 'preorder'"
                            type="button"
                            class="inline-flex h-9 items-center rounded border border-neutral-300 px-3 text-sm"
                            @click="rejectTargetId = order.id"
                        >
                            Отклонить
                        </button>
                        <button
                            v-if="order.status === 'ready' && order.deliveryMethod === 'pickup'"
                            type="button"
                            class="inline-flex h-9 items-center rounded bg-neutral-900 px-3 text-sm text-white"
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
                                class="h-9 rounded border border-neutral-300 px-3 text-sm"
                            >
                            <button
                                type="submit"
                                class="inline-flex h-9 items-center rounded bg-red-700 px-3 text-sm text-white"
                            >
                                Списать
                            </button>
                        </form>
                                </div>
                            </td>
                        </tr>
                        <tr
                            v-if="isOpen(order.id)"
                            class="border-b border-neutral-100 bg-neutral-50"
                        >
                            <td
                                :colspan="readOnly ? 11 : 9"
                                class="px-3 py-3"
                            >
                    <div class="mb-3 flex flex-wrap gap-1">
                        <button
                            v-for="item in detailTabs"
                            :key="item.id"
                            type="button"
                            class="inline-flex h-9 items-center rounded px-3 text-sm"
                            :class="detailTab === item.id ? 'bg-neutral-900 text-white' : 'bg-white text-neutral-700'"
                            @click="detailTab = item.id"
                        >
                            {{ item.label }}
                        </button>
                    </div>
                    <dl
                        v-if="detailTab === 'overview'"
                        class="grid gap-3 text-sm sm:grid-cols-2"
                    >
                        <div>
                            <dt class="text-xs text-neutral-500">Идентификатор</dt>
                            <dd>#{{ order.id }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-500">Источник</dt>
                            <dd>{{ sourceLabel(order.source ?? "site") }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-500">Оформление</dt>
                            <dd>{{ dash(order.checkoutId) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-500">Партнёр</dt>
                            <dd>{{ dash(order.partnerCode) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-500">Внешний ID заказа</dt>
                            <dd>{{ dash(order.externalOrderId) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-500">Статус</dt>
                            <dd>{{ statusLabel(order.status) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-500">Сумма товаров</dt>
                            <dd>{{ order.total }} ₽</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-500">Создан</dt>
                            <dd>{{ dash(order.createdAt) }}</dd>
                        </div>
                    </dl>
                    <table
                        v-else-if="detailTab === 'cart'"
                        class="w-full text-left text-sm"
                    >
                        <thead class="text-xs text-neutral-500">
                            <tr>
                                <th class="py-1 pr-3 font-medium">Название</th>
                                <th class="py-1 pr-3 font-medium">Кол-во</th>
                                <th class="py-1 font-medium">Сумма</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="line in order.lines"
                                :key="line.name"
                            >
                                <td class="py-1 pr-3">{{ line.name }}</td>
                                <td class="py-1 pr-3">{{ line.qty }}</td>
                                <td class="py-1">{{ line.total ?? "—" }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <dl
                        v-else-if="detailTab === 'client'"
                        class="grid gap-3 text-sm sm:grid-cols-2"
                    >
                        <div>
                            <dt class="text-xs text-neutral-500">Тип клиента</dt>
                            <dd>{{ order.clientKind === "registered" ? "Авторизованный клиент" : "Гость" }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-500">ID клиента</dt>
                            <dd>{{ dash(order.clientId) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-500">Имя</dt>
                            <dd>{{ order.clientName }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-500">Телефон</dt>
                            <dd>{{ dash(order.phone) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-500">Email</dt>
                            <dd>{{ dash(order.email) }}</dd>
                        </div>
                    </dl>
                    <dl
                        v-else-if="detailTab === 'delivery'"
                        class="grid gap-3 text-sm sm:grid-cols-2"
                    >
                        <div>
                            <dt class="text-xs text-neutral-500">Способ доставки</dt>
                            <dd>{{ deliveryMethodLabel(order.deliveryMethod) }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs text-neutral-500">Адрес</dt>
                            <dd>{{ dash(order.address) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-500">Время доставки</dt>
                            <dd>{{ dash(order.scheduledAt) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-500">Персон</dt>
                            <dd>{{ dash(order.persons) }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs text-neutral-500">Комментарий</dt>
                            <dd>{{ dash(order.comment) }}</dd>
                        </div>
                    </dl>
                    <dl
                        v-else
                        class="grid gap-3 text-sm sm:grid-cols-2"
                    >
                        <div>
                            <dt class="text-xs text-neutral-500">Способ оплаты</dt>
                            <dd>{{ paymentMethodLabel(order.payment) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-500">Сдача с</dt>
                            <dd>{{ dash(order.changeFrom) }}</dd>
                        </div>
                    </dl>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <Pager
            :page="journalPage"
            :total="journalTotal"
            :per-page="board.journalPerPage"
            @update:page="showPage"
        />
    </div>
</template>
