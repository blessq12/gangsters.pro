<script setup>
import { computed, onMounted } from "vue";
import { useSystemStore } from "../stores/systemStore";
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

const systemStore = useSystemStore();

const company = computed(() => systemStore.company);

const heroDescription = computed(() => {
    const c = company.value;
    const tag = safeTrim(c?.tagline);
    if (tag) return tag;
    const desc = safeTrim(c?.description);
    if (desc) return desc;
    return "Условия доставки и оплаты зависят от адреса и состава заказа — актуальные значения видно при оформлении. Ниже — ориентиры из настроек сервиса.";
});

const stats = computed(() => {
    if (systemStore.loadingCompany && !company.value) {
        return [
            { label: "Срок", value: "…" },
            { label: "Мин. заказ", value: "…" },
            { label: "Покрытие", value: "…" },
        ];
    }
    return buildDeliveryHeroStats(company.value);
});

const highlightMinutes = computed(() => ({
    head: deliveryHighlightMinutesHeadline(company.value),
    sub: deliveryHighlightMinutesSubline(company.value),
}));

const highlightMinOrder = computed(() => ({
    head: deliveryHighlightMinOrderHeadline(company.value),
    sub: deliveryHighlightMinOrderSubline(company.value),
}));

const paymentBlocks = buildCheckoutAlignedPaymentInfoBlocks();

const importantLead = computed(() => {
    const c = company.value;
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
    const line = formatAverageDeliveryLine(company.value);
    if (line !== "—") {
        return `Ориентир по сроку доставки — ${line}. В пиковые часы время может быть больше — это будет видно до подтверждения заказа.`;
    }
    return "В пиковые часы время может быть больше — актуальные условия видны до подтверждения заказа.";
});

onMounted(() => {
    if (!company.value && !systemStore.loadingCompany) {
        void systemStore.fetchCompany();
    }
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
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1.15fr)_minmax(280px,0.85fr)]">
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
                    Ориентир по среднему времени доставки берётся из данных компании;
                    фактический срок может отличаться в зависимости от загрузки кухни и
                    маршрута курьера.
                </p>
            </SecondaryContentBlock>

            <div class="grid gap-4">
                <article class="rounded-[1.75rem] border border-white/10 bg-[rgba(255,255,255,0.04)] p-5">
                    <p class="text-[11px] uppercase tracking-[0.22em] text-slate-400">
                        Быстрый факт
                    </p>
                    <p class="mt-3 text-3xl font-semibold text-amber-300">
                        {{ highlightMinutes.head }}
                    </p>
                    <p class="mt-1 text-sm text-slate-300">
                        {{ highlightMinutes.sub }}
                    </p>
                </article>
                <article class="rounded-[1.75rem] border border-white/10 bg-[rgba(255,255,255,0.04)] p-5">
                    <p class="text-[11px] uppercase tracking-[0.22em] text-slate-400">
                        Мин. заказ
                    </p>
                    <p class="mt-3 text-3xl font-semibold text-amber-300">
                        {{ highlightMinOrder.head }}
                    </p>
                    <p class="mt-1 text-sm text-slate-300">
                        {{ highlightMinOrder.sub }}
                    </p>
                </article>
            </div>
        </div>

        <SecondaryContentBlock
            title="Как проходит заказ"
            subtitle="СЦЕНАРИЙ ЗАКАЗА"
        >
            <div class="grid gap-4 md:grid-cols-4">
                <article class="rounded-2xl border border-white/10 bg-black/20 p-5">
                    <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-full bg-amber-400 text-black">
                        <i class="mdi mdi-cart-outline text-xl"></i>
                    </div>
                    <p class="font-medium text-slate-50">1. Оформление</p>
                    <p class="mt-2 text-sm text-slate-300">
                        Собираете заказ, указываете адрес и сразу видите базовые условия доставки.
                    </p>
                </article>

                <article class="rounded-2xl border border-white/10 bg-black/20 p-5">
                    <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-full bg-amber-400 text-black">
                        <i class="mdi mdi-check-decagram-outline text-xl"></i>
                    </div>
                    <p class="font-medium text-slate-50">2. Подтверждение</p>
                    <p class="mt-2 text-sm text-slate-300">
                        Заказ считается принятым после подтверждения оператором или системой.
                    </p>
                </article>

                <article class="rounded-2xl border border-white/10 bg-black/20 p-5">
                    <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-full bg-amber-400 text-black">
                        <i class="mdi mdi-fire-circle text-xl"></i>
                    </div>
                    <p class="font-medium text-slate-50">3. Приготовление</p>
                    <p class="mt-2 text-sm text-slate-300">
                        Кухня готовит заказ и собирает его в логичной последовательности, чтобы не терять качество.
                    </p>
                </article>

                <article class="rounded-2xl border border-white/10 bg-black/20 p-5">
                    <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-full bg-amber-400 text-black">
                        <i class="mdi mdi-moped-outline text-xl"></i>
                    </div>
                    <p class="font-medium text-slate-50">4. Доставка</p>
                    <p class="mt-2 text-sm text-slate-300">
                        Курьер везёт заказ, а вы получаете его в согласованное время без лишней путаницы.
                    </p>
                </article>
            </div>
        </SecondaryContentBlock>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
            <SecondaryContentBlock
                title="Способы оплаты"
                subtitle="ОПЛАТА"
            >
                <div class="grid gap-3">
                    <div
                        v-for="block in paymentBlocks"
                        :key="block.id"
                        class="flex items-start gap-4 rounded-2xl border border-white/10 bg-black/20 p-4"
                    >
                        <i
                            class="text-2xl text-amber-300"
                            :class="block.icon"
                        ></i>
                        <div>
                            <p class="font-medium text-slate-50">
                                {{ block.title }}
                            </p>
                            <p class="mt-1 text-sm text-slate-300">
                                {{ block.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </SecondaryContentBlock>

            <article class="overflow-hidden rounded-[1.75rem] border border-amber-400/20 bg-[linear-gradient(180deg,rgba(251,191,36,0.1),rgba(255,255,255,0.03))] p-6">
                <p class="text-xs uppercase tracking-[0.26em] text-amber-200">
                    Важно
                </p>
                <p class="mt-4 text-2xl font-semibold leading-tight text-slate-50">
                    {{ importantLead }}
                </p>
                <p class="mt-3 text-sm leading-relaxed text-slate-300">
                    {{ importantSub }}
                </p>
                <div class="mt-5 flex flex-wrap gap-2 text-xs text-slate-200">
                    <span class="rounded-full border border-white/10 bg-black/30 px-3 py-1.5">
                        Прозрачные условия
                    </span>
                    <span class="rounded-full border border-white/10 bg-black/30 px-3 py-1.5">
                        Без скрытых комиссий
                    </span>
                    <span class="rounded-full border border-white/10 bg-black/30 px-3 py-1.5">
                        Условия по адресу
                    </span>
                </div>
            </article>
        </div>
    </SecondaryPageLayout>
</template>
