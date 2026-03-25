<script setup>
import { computed, onMounted, watch } from "vue";
import { useUserStore } from "../../stores/userStore";
import { useOrderStore } from "../../stores/orderStore";
import { formatOrderDate, formatOrderMoneyKopecks } from "../../utils/order/orderDisplay";

const emit = defineEmits(["logout"]);

const userStore = useUserStore();
const orderStore = useOrderStore();

const fullName = computed(() => userStore.profile.name || "Гость Gangsters");
const phone = computed(() => userStore.profile.phone || "+7 (___) ___‑__‑__");
const email = computed(() => userStore.profile.email || "email не указан");
const isAuthenticated = computed(() => !!userStore.token && !!userStore.profile.id);

const stats = computed(() => orderStore.clientOrderStats);

function refreshOrdersIfNeeded() {
    if (!isAuthenticated.value) return;
    void orderStore.fetchOrders().catch(() => {});
}

onMounted(refreshOrdersIfNeeded);

watch(isAuthenticated, (ok) => {
    if (ok) refreshOrdersIfNeeded();
});

function handleLogoutClick() {
    userStore.clearAuth();
    emit("logout");
}
</script>

<template>
    <div class="space-y-4 text-slate-50">
        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
            Контакты в профиле
        </p>

        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div
                    class="flex h-12 w-12 items-center justify-center rounded-full border border-amber-400/40 bg-black/70 text-base font-semibold text-amber-200 shadow-[0_0_20px_rgba(251,191,36,0.6)]"
                >
                    {{ fullName[0] ?? "G" }}
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-50">
                        {{ fullName }}
                    </p>
                    <p class="text-xs text-slate-300/85">
                        {{ phone }}
                    </p>
                    <p class="text-xs text-slate-400">
                        {{ email }}
                    </p>
                </div>
            </div>

            <button
                v-if="isAuthenticated"
                type="button"
                class="ml-3 inline-flex items-center rounded-full border border-red-500/70 bg-red-500/10 px-3 py-1 text-[11px] font-semibold text-red-200 transition hover:bg-red-500/20 hover:text-red-100"
                @click="handleLogoutClick"
            >
                Выйти
            </button>
        </div>

        <div
            v-if="isAuthenticated"
            class="space-y-2"
        >
            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                Статистика заказов
            </p>
            <p class="text-[10px] leading-snug text-slate-500">
                Считаем все оформленные заказы из твоей истории (без фильтра по статусу).
            </p>

            <div
                v-if="orderStore.loading.list && !orderStore.orders.length"
                class="rounded-2xl border border-white/10 bg-black/30 px-3 py-4 text-center text-xs text-slate-400"
            >
                Считаем вашу историю…
            </div>

            <div
                v-else-if="orderStore.error.list && !orderStore.orders.length"
                class="rounded-2xl border border-amber-400/25 bg-black/35 px-3 py-3 text-[11px] text-slate-400"
            >
                <span class="text-amber-200/90">{{ orderStore.error.list }}</span>
                <span class="mt-1 block text-slate-500">
                    Статистика появится после успешной загрузки списка заказов.
                </span>
            </div>

            <div
                v-else-if="!stats.count"
                class="rounded-2xl border border-dashed border-white/12 bg-black/25 px-3 py-4 text-center text-xs text-slate-400"
            >
                Заказов ещё не было. Первый заказ — и тут оживут суммы и счётчики.
            </div>

            <div
                v-else
                class="grid grid-cols-2 gap-2"
            >
                <div
                    class="rounded-2xl border border-white/10 bg-black/35 px-3 py-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.04)]"
                >
                    <p class="text-[10px] font-medium uppercase tracking-wide text-slate-500">
                        Заказов всего
                    </p>
                    <p class="mt-1 text-xl font-semibold tabular-nums text-amber-300">
                        {{ stats.count }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-white/10 bg-black/35 px-3 py-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.04)]"
                >
                    <p class="text-[10px] font-medium uppercase tracking-wide text-slate-500">
                        Сумма заказов
                    </p>
                    <p class="mt-1 text-lg font-semibold tabular-nums text-slate-50">
                        {{ formatOrderMoneyKopecks(stats.totalKopecks) }}&nbsp;₽
                    </p>
                </div>
                <div
                    class="col-span-2 rounded-2xl border border-white/10 bg-black/35 px-3 py-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.04)]"
                >
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <div>
                            <p class="text-[10px] font-medium uppercase tracking-wide text-slate-500">
                                Последний заказ
                            </p>
                            <p class="mt-0.5 text-xs text-slate-200">
                                {{ formatOrderDate(stats.lastOrderAt) }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-medium uppercase tracking-wide text-slate-500">
                                Средний чек
                            </p>
                            <p class="mt-0.5 text-sm font-semibold tabular-nums text-amber-200/95">
                                {{ formatOrderMoneyKopecks(stats.averageKopecks) }}&nbsp;₽
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <p class="text-[11px] leading-relaxed text-slate-500">
            Адреса — вкладка «Адреса», список заказов — «Заказы», правки контактов — «Данные».
        </p>
    </div>
</template>
