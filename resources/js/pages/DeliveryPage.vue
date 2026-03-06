<template>
    <SecondaryPageLayout
        title="Оплата и доставка"
        eyebrow="Правила доставки"
        description="Доставляем быстро, без мутных условий и скрытых комиссий. Ниже — всё, что нужно знать о сроках, оплате и сценарии получения заказа."
        :breadcrumbs="['Главная', 'Оплата и доставка']"
        hero-image="/images/banners/banner2.jpeg"
        :stats="[
            { label: 'Срок', value: '45–90 минут' },
            { label: 'Бесплатно', value: 'от 1500 ₽' },
            { label: 'Оплата', value: 'Карта / СБП / наличные' },
        ]"
    >
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1.15fr)_minmax(280px,0.85fr)]">
            <SecondaryContentBlock
                title="Зоны и сроки доставки"
                subtitle="КАК ЭТО РАБОТАЕТ"
            >
                <p>
                    Доставляем по городу и пригороду. Доступность зоны, минимальная
                    сумма заказа и ориентировочное время система показывает прямо при
                    оформлении, как только указан адрес.
                </p>
                <p>
                    Стандартный диапазон ожидания — от 45 до 90 минут. В часы пик
                    срок может увеличиться, но мы не морозимся и показываем это
                    заранее, чтобы у человека не было ложных ожиданий.
                </p>
            </SecondaryContentBlock>

            <div class="grid gap-4">
                <article class="rounded-[1.75rem] border border-white/10 bg-[rgba(255,255,255,0.04)] p-5">
                    <p class="text-[11px] uppercase tracking-[0.22em] text-slate-400">
                        Быстрый факт
                    </p>
                    <p class="mt-3 text-3xl font-semibold text-amber-300">45–90</p>
                    <p class="mt-1 text-sm text-slate-300">
                        минут — базовый ориентир по доставке в пределах основной зоны.
                    </p>
                </article>
                <article class="rounded-[1.75rem] border border-white/10 bg-[rgba(255,255,255,0.04)] p-5">
                    <p class="text-[11px] uppercase tracking-[0.22em] text-slate-400">
                        Бонус
                    </p>
                    <p class="mt-3 text-3xl font-semibold text-amber-300">1500 ₽</p>
                    <p class="mt-1 text-sm text-slate-300">
                        и выше — доставка по городу за наш счёт.
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
                    <div class="flex items-start gap-4 rounded-2xl border border-white/10 bg-black/20 p-4">
                        <i class="mdi mdi-cash text-2xl text-amber-300"></i>
                        <div>
                            <p class="font-medium text-slate-50">Наличными</p>
                            <p class="mt-1 text-sm text-slate-300">
                                Удобно, если предпочитаете рассчитываться при получении заказа.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4 rounded-2xl border border-white/10 bg-black/20 p-4">
                        <i class="mdi mdi-credit-card-outline text-2xl text-amber-300"></i>
                        <div>
                            <p class="font-medium text-slate-50">Банковской картой</p>
                            <p class="mt-1 text-sm text-slate-300">
                                Онлайн при оформлении либо при получении, если такой способ доступен.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4 rounded-2xl border border-white/10 bg-black/20 p-4">
                        <i class="mdi mdi-cellphone-nfc text-2xl text-amber-300"></i>
                        <div>
                            <p class="font-medium text-slate-50">СБП и онлайн-оплата</p>
                            <p class="mt-1 text-sm text-slate-300">
                                Быстрый и безопасный вариант для тех, кто хочет закрыть вопрос сразу.
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
                    Бесплатная доставка начинается от 1500 ₽.
                </p>
                <p class="mt-3 text-sm leading-relaxed text-slate-300">
                    Подробные условия зависят от адреса и текущих акций. Если за окном
                    пик или очень дальняя зона, стоимость и сроки показываются до подтверждения заказа.
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
