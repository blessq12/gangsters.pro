<script setup>
import { computed } from "vue";
import { useCompanyReadModel } from "../features/company/useCompanyReadModel";
import { useDeliveryReadModel } from "../features/delivery/useDeliveryReadModel";
import {
    buildCheckoutAlignedPaymentInfoBlocks,
    buildDeliveryHeroStats,
    deliveryHighlightMinOrderHeadline,
    deliveryHighlightMinOrderSubline,
    deliveryHighlightMinutesHeadline,
    deliveryHighlightMinutesSubline,
    formatAverageDeliveryLine,
    formatDeliveryFeeRublesLine,
    formatMinOrderRublesLine,
    kopecksToRublesOptional,
} from "../utils/system/companyDeliveryFacts";
import { safeTrim } from "../utils/system/companyDisplay";
import { useAppDesign } from "../design/useAppDesign";

const { profile: profileRef } = useCompanyReadModel({ autoload: true });
const { facts: factsRef, loading: deliveryLoading } = useDeliveryReadModel({
    autoload: true,
});

const dv = useAppDesign().components.pages.delivery;

const profile = computed(() => profileRef.value);
const facts = computed(() => factsRef.value);

const heroDescription = computed(() => {
    const c = profile.value;
    const tag = safeTrim(c?.tagline);
    if (tag) return tag;
    const desc = safeTrim(c?.description);
    if (desc) return desc;
    return "Условия доставки и оплаты зависят от адреса и состава заказа — актуальные значения видно при оформлении. Ниже — ориентиры из настроек сервиса.";
});

const stats = computed(() => {
    if (deliveryLoading.value && !facts.value) {
        return [
            { label: "Срок", value: "…" },
            { label: "Мин. заказ", value: "…" },
        ];
    }
    return buildDeliveryHeroStats(facts.value);
});

const highlightMinutes = computed(() => ({
    head: deliveryHighlightMinutesHeadline(facts.value),
    sub: deliveryHighlightMinutesSubline(facts.value),
}));

const highlightMinOrder = computed(() => ({
    head: deliveryHighlightMinOrderHeadline(facts.value),
    sub: deliveryHighlightMinOrderSubline(facts.value),
}));

const paymentBlocks = buildCheckoutAlignedPaymentInfoBlocks();

const importantLead = computed(() => {
    const c = facts.value;
    const minRub = kopecksToRublesOptional(c?.min_order_amount_kopecks);
    const feeRub = kopecksToRublesOptional(c?.delivery_fee_kopecks);
    if (minRub != null && feeRub != null) {
        return `Минимальная сумма заказа — ${formatMinOrderRublesLine(c)}, стоимость доставки — ${formatDeliveryFeeRublesLine(c)}.`;
    }
    if (minRub != null) {
        return `Минимальная сумма заказа — ${formatMinOrderRublesLine(c)}.`;
    }
    if (feeRub != null) {
        return `Стоимость доставки — ${formatDeliveryFeeRublesLine(c)}.`;
    }
    return "Итоговые суммы и сроки уточняются при оформлении заказа и зависят от адреса.";
});

const importantSub = computed(() => {
    const line = formatAverageDeliveryLine(facts.value);
    if (line !== "—") {
        return `Ориентир по сроку доставки — ${line}. В пиковые часы время может быть больше — это будет видно до подтверждения заказа.`;
    }
    return "В пиковые часы время может быть больше — актуальные условия видны до подтверждения заказа.";
});
</script>

<template>
    <SecondaryPageLayout
        title="Оплата и доставка"
        eyebrow="Правила доставки"
        :description="heroDescription"
        :breadcrumbs="['Главная', 'Оплата и доставка']"
        hero-image="/images/banners/banner2.jpeg"
        :stats="stats"
    >
        <div :class="dv.gridTop">
            <SecondaryContentBlock
                title="Зоны и сроки доставки"
                subtitle="КАК ЭТО РАБОТАЕТ"
            >
                <p>
                    Доставляем в зону покрытия, указанную в настройках сервиса. Точную
                    доступность по адресу, минимальную сумму и ориентировочное время
                    вы видите при оформлении, как только указан адрес.
                </p>
                <p>
                    Ориентир по среднему времени доставки берётся из данных доставки;
                    фактический срок может отличаться в зависимости от загрузки кухни и
                    маршрута курьера.
                </p>
            </SecondaryContentBlock>

            <div :class="dv.factsStack">
                <article :class="dv.highlightCard">
                    <p :class="dv.highlightKicker">
                        Быстрый факт
                    </p>
                    <p :class="dv.highlightValue">
                        {{ highlightMinutes.head }}
                    </p>
                    <p :class="dv.highlightSub">
                        {{ highlightMinutes.sub }}
                    </p>
                </article>
                <article :class="dv.highlightCard">
                    <p :class="dv.highlightKicker">
                        Мин. заказ
                    </p>
                    <p :class="dv.highlightValue">
                        {{ highlightMinOrder.head }}
                    </p>
                    <p :class="dv.highlightSub">
                        {{ highlightMinOrder.sub }}
                    </p>
                </article>
            </div>
        </div>

        <SecondaryContentBlock
            title="Как проходит заказ"
            subtitle="СЦЕНАРИЙ ЗАКАЗА"
        >
            <div :class="dv.stepsGrid">
                <article :class="dv.stepCard">
                    <div :class="dv.stepIconWrap">
                        <i class="mdi mdi-cart-outline text-xl"></i>
                    </div>
                    <p :class="dv.stepTitle">1. Оформление</p>
                    <p :class="dv.stepBody">
                        Собираете заказ, указываете адрес и сразу видите базовые условия доставки.
                    </p>
                </article>

                <article :class="dv.stepCard">
                    <div :class="dv.stepIconWrap">
                        <i class="mdi mdi-check-decagram-outline text-xl"></i>
                    </div>
                    <p :class="dv.stepTitle">2. Подтверждение</p>
                    <p :class="dv.stepBody">
                        Заказ считается принятым после подтверждения оператором или системой.
                    </p>
                </article>

                <article :class="dv.stepCard">
                    <div :class="dv.stepIconWrap">
                        <i class="mdi mdi-fire-circle text-xl"></i>
                    </div>
                    <p :class="dv.stepTitle">3. Приготовление</p>
                    <p :class="dv.stepBody">
                        Кухня готовит заказ и собирает его в логичной последовательности, чтобы не терять качество.
                    </p>
                </article>

                <article :class="dv.stepCard">
                    <div :class="dv.stepIconWrap">
                        <i class="mdi mdi-moped-outline text-xl"></i>
                    </div>
                    <p :class="dv.stepTitle">4. Доставка</p>
                    <p :class="dv.stepBody">
                        Курьер везёт заказ, а вы получаете его в согласованное время без лишней путаницы.
                    </p>
                </article>
            </div>
        </SecondaryContentBlock>

        <div :class="dv.gridBottom">
            <SecondaryContentBlock
                title="Способы оплаты"
                subtitle="ОПЛАТА"
            >
                <div class="grid gap-3">
                    <div
                        v-for="block in paymentBlocks"
                        :key="block.id"
                        :class="dv.paymentRow"
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
                        </div>
                    </div>
                </div>
            </SecondaryContentBlock>

            <article :class="dv.importantArticle">
                <p :class="dv.importantEyebrow">
                    Важно
                </p>
                <p :class="dv.importantTitle">
                    {{ importantLead }}
                </p>
                <p :class="dv.importantBody">
                    {{ importantSub }}
                </p>
                <div :class="dv.chipsRow">
                    <span :class="dv.chip">
                        Прозрачные условия
                    </span>
                    <span :class="dv.chip">
                        Без скрытых комиссий
                    </span>
                    <span :class="dv.chip">
                        Условия по адресу
                    </span>
                </div>
            </article>
        </div>
    </SecondaryPageLayout>
</template>
