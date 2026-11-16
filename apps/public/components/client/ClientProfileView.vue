<script setup>
import { computed } from "vue";
import { storeToRefs } from "pinia";
import { useOrdersReadModel } from "../../modules/client/application/useOrdersReadModel";
import { useUserStore } from "../../modules/client/store/userStore";
import {
    formatMembershipDurationRu,
    formatOrderMoneyRubles,
} from "../../modules/client/application/orderDisplay";
import { useAppDesign } from "../../design/useAppDesign";

const pv = useAppDesign().components.client.profileView;

const userStore = useUserStore();
const { profile } = storeToRefs(userStore);
const { stats, loading, error } = useOrdersReadModel({ autoload: true });

const membershipLabel = computed(() =>
    formatMembershipDurationRu(profile.value?.created_at),
);

const averageCheckLabel = computed(() => {
    if (!stats.value.count) {
        return "—";
    }
    return `${formatOrderMoneyRubles(stats.value.averageOrderRubles)} ₽`;
});
</script>

<template>
    <div :class="pv.root">
        <p :class="pv.welcome">
            Спасибо, что ты с нами. Мы это ценим.
        </p>

        <div :class="pv.statsSection">
            <div
                v-if="loading && !stats.count"
                :class="pv.statLoading"
            >
                Считаем…
            </div>

            <div
                v-else-if="error && !stats.count"
                :class="pv.statError"
            >
                {{ error }}
            </div>

            <div
                v-else
                :class="pv.statGrid"
            >
                <div :class="pv.statCard">
                    <p :class="pv.statLabel">
                        С нами
                    </p>
                    <p :class="pv.statValueMain">
                        {{ membershipLabel || "—" }}
                    </p>
                </div>
                <div :class="pv.statCard">
                    <p :class="pv.statLabel">
                        Заказов
                    </p>
                    <p :class="pv.statValueAccent">
                        {{ stats.count }}
                    </p>
                </div>
                <div :class="pv.statCardWide">
                    <p :class="pv.statLabel">
                        Средний чек
                    </p>
                    <p :class="pv.statValueMain">
                        {{ averageCheckLabel }}
                    </p>
                </div>
            </div>
        </div>

        <div :class="pv.offersBlock">
            <p :class="pv.offersTitle">
                Персональные предложения
            </p>
            <p :class="pv.offersHint">
                Проверяй этот блок — тут будут появляться предложения, доступные
                только тебе.
            </p>
        </div>
    </div>
</template>
