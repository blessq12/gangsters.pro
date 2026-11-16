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

/** Порог бесплатной доставки и базовая плата ниже порога (руб.). */
const FREE_DELIVERY_THRESHOLD_RUBLES = 1200;
const BELOW_THRESHOLD_DELIVERY_FEE_RUBLES = 200;

const deliveryPricingRules = [
    {
        id: "free-zone-from-threshold",
        title: "Бесплатная зона, заказ от 1 200 ₽",
        body: "Доставка полностью бесплатная — только в зоне бесплатной доставки и при сумме заказа от 1 200 ₽.",
    },
    {
        id: "free-zone-below-threshold",
        title: "Бесплатная зона, заказ меньше 1 200 ₽",
        body: "Если адрес в зоне бесплатной доставки, но сумма заказа ниже 1 200 ₽, доставка стоит 200 ₽.",
    },
    {
        id: "paid-zone-from-threshold",
        title: "Платная зона, заказ от 1 200 ₽",
        body: "Оплачивается только тариф выбранной платной зоны. Стоимость зоны смотрите на карте — нажмите на нужный район.",
    },
    {
        id: "paid-zone-below-threshold",
        title: "Платная зона, заказ меньше 1 200 ₽",
        body: "Суммируются 200 ₽ (плата за заказ ниже 1 200 ₽) и тариф платной зоны с карты.",
    },
];
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

        <SecondaryContentBlock title="Правила стоимости доставки">
            <p class="mb-4 text-sm text-app-muted">
                Стоимость зависит от зоны на карте и суммы заказа.
                Порог для бесплатной доставки —
                {{ FREE_DELIVERY_THRESHOLD_RUBLES.toLocaleString("ru-RU") }} ₽.
                Ниже порога в любой зоне добавляется
                {{ BELOW_THRESHOLD_DELIVERY_FEE_RUBLES.toLocaleString("ru-RU") }} ₽.
            </p>
            <div :class="dv.stepsGrid">
                <article
                    v-for="(rule, index) in deliveryPricingRules"
                    :key="rule.id"
                    :class="dv.stepCard"
                >
                    <div :class="dv.stepIconWrap">
                        {{ index + 1 }}
                    </div>
                    <p :class="dv.stepTitle">
                        {{ rule.title }}
                    </p>
                    <p :class="dv.stepBody">
                        {{ rule.body }}
                    </p>
                </article>
            </div>
        </SecondaryContentBlock>

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
