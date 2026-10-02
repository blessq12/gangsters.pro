<script setup>
import { buildCheckoutAlignedPaymentInfoBlocks } from "../modules/content/application/company";
import { useAppDesign } from "../design/useAppDesign";

const dv = useAppDesign().components.pages.delivery;
const paymentBlocks = buildCheckoutAlignedPaymentInfoBlocks();
</script>

<template>
    <SecondaryPageLayout
        title="Оплата и доставка"
        eyebrow="Правила доставки"
        description="Мы принимаем и доставляем заказы с 11:00 до 23:00."
        :breadcrumbs="['Главная', 'Оплата и доставка']"
        hero-image="/images/delivery_banner.jpg"
        :stats="[
            { label: 'Приём заказов', value: '11:00–23:00' },
            { label: 'Срок', value: '90–120 мин' },
        ]"
    >
        <div :class="dv.gridTop">
            <SecondaryContentBlock title="Зоны и сроки">
                <p>
                    Доставка осуществляется в пределах города и ближайших
                    пригородов. Стоимость доставки зависит от расстояния.
                </p>
                <p>
                    Мы стараемся доставить ваш заказ максимально быстро: среднее
                    время доставки — от 90 до 120 минут, в зависимости от
                    загруженности дорог и удалённости района.
                </p>
            </SecondaryContentBlock>

            <div :class="dv.factsStack">
                <article :class="dv.highlightCard">
                    <p :class="dv.highlightKicker">
                        Режим
                    </p>
                    <p :class="dv.highlightValue">
                        11:00–23:00
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
                        90–120
                    </p>
                    <p :class="dv.highlightSub">
                        минут в среднем, зависит от дороги и района.
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
                    Приём и доставка — с 11:00 до 23:00
                </p>
                <p :class="dv.importantBody">
                    Зона — город и ближайшие пригороды. Стоимость зависит от
                    расстояния; ориентир по сроку — 90–120 минут.
                </p>
            </article>
        </div>
    </SecondaryPageLayout>
</template>
