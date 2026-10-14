<script setup>
import { computed } from "vue";
import { storeToRefs } from "pinia";
import { useContentStore } from "../modules/content/store";
import {
    averageDeliveryMinutesOrNull,
    buildCheckoutAlignedPaymentInfoBlocks,
    formatAverageDeliveryLine,
    formatTodayWorkScheduleLine,
} from "../modules/content/application/company";
import { useAppDesign } from "../design/useAppDesign";

const dv = useAppDesign().components.pages.delivery;
const paymentBlocks = buildCheckoutAlignedPaymentInfoBlocks();

const contentStore = useContentStore();
const { profile, deliveryFacts: facts } = storeToRefs(contentStore);

const todayScheduleLine = computed(
    () => formatTodayWorkScheduleLine(profile.value, new Date()) || "—",
);

const averageDeliveryLine = computed(() =>
    formatAverageDeliveryLine(facts.value),
);

const averageDeliveryMinutes = computed(() =>
    averageDeliveryMinutesOrNull(facts.value),
);

const heroDescription = computed(() => {
    const line = formatTodayWorkScheduleLine(profile.value, new Date());
    if (!line) {
        return "Мы принимаем и доставляем заказы по расписанию текущего дня.";
    }
    return `Мы принимаем и доставляем заказы (${line}).`;
});

const heroStats = computed(() => [
    { label: "Приём заказов", value: todayScheduleLine.value },
    { label: "Срок", value: averageDeliveryLine.value },
]);

const importantTitle = computed(() => {
    const line = formatTodayWorkScheduleLine(profile.value, new Date());
    if (!line) {
        return "Приём и доставка — по расписанию текущего дня";
    }
    return `Приём и доставка — ${line}`;
});

const importantBody = computed(() => {
    const eta = averageDeliveryLine.value;
    return `Зоны доставки отмечены на карте — нажмите на зону, чтобы увидеть стоимость и условия. Ориентир по сроку — ${eta}.`;
});

const deliveryEtaSub = computed(() => {
    if (averageDeliveryMinutes.value == null) {
        return "среднее время зависит от дороги и района.";
    }
    return "минут в среднем, зависит от дороги и района.";
});

const zonesIntroEta = computed(() => {
    const minutes = averageDeliveryMinutes.value;
    if (minutes == null) {
        return averageDeliveryLine.value;
    }
    return `около ${minutes} минут`;
});
</script>

<template>
    <SecondaryPageLayout
        title="Оплата и доставка"
        eyebrow="Правила доставки"
        :description="heroDescription"
        :breadcrumbs="['Главная', 'Оплата и доставка']"
        hero-image="/images/delivery_banner.jpg"
        :stats="heroStats"
    >
        <div :class="dv.gridTop">
            <SecondaryContentBlock title="Зоны и сроки">
                <p>
                    Доставка осуществляется в пределах зон на карте ниже.
                    Нажмите на зону, чтобы увидеть стоимость доставки и условия.
                </p>
                <p>
                    Мы стараемся доставить ваш заказ максимально быстро: среднее
                    время доставки — {{ zonesIntroEta }}, в зависимости от
                    загруженности дорог и удалённости района.
                </p>
            </SecondaryContentBlock>

            <div :class="dv.factsStack">
                <article :class="dv.highlightCard">
                    <p :class="dv.highlightKicker">
                        Режим
                    </p>
                    <p :class="dv.highlightValue">
                        {{ todayScheduleLine }}
                    </p>
                    <p :class="dv.highlightSub">
                        Принимаем и доставляем заказы.
                    </p>
                </article>
                <article :class="dv.highlightCard">
                    <p :class="dv.highlightKicker">
                        Срок
                    </p>
                    <p :class="dv.highlightValue">
                        {{
                            averageDeliveryMinutes != null
                                ? averageDeliveryMinutes
                                : averageDeliveryLine
                        }}
                    </p>
                    <p :class="dv.highlightSub">
                        {{ deliveryEtaSub }}
                    </p>
                </article>
            </div>
        </div>

        <DeliveryZoneMapBlock />

        <div :class="dv.gridBottom">
            <SecondaryContentBlock title="Способы оплаты">
                <p class="mb-4 text-sm text-app-muted">
                    В нашем магазине вы можете оплатить заказ следующими
                    способами:
                </p>
                <div class="grid gap-3">
                    <div
                        v-for="block in paymentBlocks"
                        :key="block.id"
                        :class="
                            block.unavailable
                                ? dv.paymentRowUnavailable
                                : dv.paymentRow
                        "
                    >
                        <i
                            class="text-2xl text-app-accent"
                            :class="block.icon"
                        ></i>
                        <div>
                            <p :class="dv.paymentTitle">
                                {{ block.title }}
                            </p>
                            <p :class="dv.paymentBody">
                                {{ block.description }}
                            </p>
                            <p
                                v-if="block.unavailable"
                                :class="dv.paymentBadge"
                            >
                                Временно недоступен
                            </p>
                        </div>
                    </div>
                </div>
            </SecondaryContentBlock>

            <article :class="dv.importantArticle">
                <p :class="dv.importantEyebrow">
                    Важно
                </p>
                <p :class="dv.importantTitle">
                    {{ importantTitle }}
                </p>
                <p :class="dv.importantBody">
                    {{ importantBody }}
                </p>
            </article>
        </div>
    </SecondaryPageLayout>
</template>
