<script setup>
import { computed } from "vue";
import { useClientCommands, useClientReadModel } from "../../features/client/useClient";
import { useOrdersReadModel } from "../../features/orders/useOrdersReadModel";
import { formatOrderDate, formatOrderMoneyRubles } from "../../utils/order/orderDisplay";
import { useAppDesign } from "../../design/useAppDesign";

const emit = defineEmits(["logout"]);

const pv = useAppDesign().components.client.profileView;

const clientReadModel = useClientReadModel();
const clientCommands = useClientCommands();
const { stats, loading, error } = useOrdersReadModel({ autoload: true });

const fullName = computed(
    () => clientReadModel.profile.value.name || "Гость Gangsters",
);
const phone = computed(
    () => clientReadModel.profile.value.phone || "+7 (___) ___‑__‑__",
);
const email = computed(
    () => clientReadModel.profile.value.email || "email не указан",
);
const isAuthenticated = computed(() => clientReadModel.isAuthenticated.value);

function handleLogoutClick() {
    clientCommands.clearAuth();
    emit("logout");
}
</script>

<template>
    <div :class="pv.root">
        <p :class="pv.sectionKicker">
            Контакты в профиле
        </p>

        <div :class="pv.headerRow">
            <div :class="pv.userRow">
                <div :class="pv.avatar">
                    {{ fullName[0] ?? "G" }}
                </div>
                <div :class="pv.userTextCol">
                    <p :class="pv.nameStrong">
                        {{ fullName }}
                    </p>
                    <p :class="pv.phoneLine">
                        {{ phone }}
                    </p>
                    <p :class="pv.emailLine">
                        {{ email }}
                    </p>
                </div>
            </div>

            <button
                v-if="isAuthenticated"
                type="button"
                :class="pv.btnLogout"
                @click="handleLogoutClick"
            >
                Выйти
            </button>
        </div>

        <div
            v-if="isAuthenticated"
            :class="pv.statsSection"
        >
            <p :class="pv.sectionKicker">
                Статистика заказов
            </p>
            <p :class="pv.statsHint">
                Считаем все оформленные заказы из твоей истории (без фильтра по статусу).
            </p>

            <div
                v-if="loading && !stats.count"
                :class="pv.statLoading"
            >
                Считаем вашу историю…
            </div>

            <div
                v-else-if="error && !stats.count"
                :class="pv.statError"
            >
                <span :class="pv.statErrorAccent">{{ error }}</span>
                <span :class="pv.statErrorSub">
                    Статистика появится после успешной загрузки списка заказов.
                </span>
            </div>

            <div
                v-else-if="!stats.count"
                :class="pv.statEmpty"
            >
                Заказов ещё не было. Первый заказ — и тут оживут суммы и счётчики.
            </div>

            <div
                v-else
                :class="pv.statGrid"
            >
                <div :class="pv.statCard">
                    <p :class="pv.statLabel">
                        Заказов всего
                    </p>
                    <p :class="pv.statValueAccent">
                        {{ stats.count }}
                    </p>
                </div>
                <div :class="pv.statCard">
                    <p :class="pv.statLabel">
                        Сумма заказов
                    </p>
                    <p :class="pv.statValueMain">
                        {{ formatOrderMoneyRubles(stats.totalOrderSpendRubles) }}&nbsp;₽
                    </p>
                </div>
                <div :class="pv.statCardWide">
                    <div :class="pv.statLastRow">
                        <div>
                            <p :class="pv.statLabel">
                                Последний заказ
                            </p>
                            <p :class="pv.statDateLine">
                                {{ formatOrderDate(stats.lastOrderAt) }}
                            </p>
                        </div>
                        <div :class="pv.statRightCol">
                            <p :class="pv.statLabel">
                                Средний чек
                            </p>
                            <p :class="pv.avgValue">
                                {{ formatOrderMoneyRubles(stats.averageOrderRubles) }}&nbsp;₽
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <p :class="pv.footerHint">
            Адреса — вкладка «Адреса», список заказов — «Заказы», правки контактов — «Данные».
        </p>
    </div>
</template>
