<template>
    <SecondaryPageLayout
        title="О компании"
        eyebrow="История бренда"
        description="Gangsters — это доставка с характером: тёмная эстетика, сочные позиции в меню и сервис, который не рассыпается на мелочах."
        :breadcrumbs="['Главная', 'О компании']"
        hero-image="/images/banners/banner1.jpeg"
        :stats="[
            { label: 'Кухни', value: '3 направления' },
            { label: 'Фокус', value: 'Скорость и вкус' },
            { label: 'Тон', value: 'Гангстерский вайб' },
        ]"
    >
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1.2fr)_minmax(280px,0.8fr)]">
            <SecondaryContentBlock
                title="Кто мы такие"
                subtitle="ГАСТРО-АТМОСФЕРА"
            >
                <p>
                    Gangsters собрали под одним брендом несколько гастро-настроений:
                    роллы, горячие позиции, комбо и ночные заказы. Нам важен не
                    только вкус, но и то, как человек чувствует себя, когда
                    открывает заказ.
                </p>
                <p>
                    Поэтому у нас всё работает в одной системе: графитовый визуал,
                    тёплые янтарные акценты, читаемое меню, живая подача и сервис
                    без хаоса. Короче, не пластиковая витрина, а еда с характером.
                </p>
            </SecondaryContentBlock>

            <div class="grid gap-4">
                <article class="overflow-hidden rounded-[1.75rem] border border-white/10 bg-[rgba(255,255,255,0.04)] p-5 shadow-[0_16px_50px_rgba(0,0,0,0.35)]">
                    <div class="mb-4 flex items-center justify-between">
                        <span class="inline-flex rounded-full border border-amber-400/30 bg-amber-400/10 px-3 py-1 text-[11px] uppercase tracking-[0.24em] text-amber-200">
                            Характер
                        </span>
                        <i class="mdi mdi-crown-outline text-2xl text-amber-300"></i>
                    </div>
                    <p class="text-lg font-semibold text-slate-50">
                        Вкус, скорость и атмосфера.
                    </p>
                    <p class="mt-2 text-sm leading-relaxed text-slate-300">
                        Мы строим продукт так, чтобы он был заметным визуально и
                        понятным в использовании: без лишней мишуры, но с жирным
                        акцентом на качестве.
                    </p>
                </article>

                <article class="rounded-[1.75rem] border border-white/10 bg-[rgba(255,255,255,0.03)] p-5">
                    <p class="text-xs uppercase tracking-[0.24em] text-slate-400">
                        Что отличает
                    </p>
                    <div class="mt-4 flex flex-wrap gap-2 text-xs text-slate-200">
                        <span class="rounded-full border border-white/10 bg-black/30 px-3 py-1.5">
                            Яркие комбо
                        </span>
                        <span class="rounded-full border border-white/10 bg-black/30 px-3 py-1.5">
                            Ночной режим
                        </span>
                        <span class="rounded-full border border-white/10 bg-black/30 px-3 py-1.5">
                            Живой брендинг
                        </span>
                        <span class="rounded-full border border-white/10 bg-black/30 px-3 py-1.5">
                            Быстрая сборка
                        </span>
                    </div>
                </article>
            </div>
        </div>

        <SecondaryContentBlock
            title="Наши принципы"
            subtitle="НАШИ ПРИНЦИПЫ"
        >
            <div class="grid gap-4 md:grid-cols-3">
                <article class="rounded-2xl border border-white/10 bg-black/20 p-5">
                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-full bg-amber-400 text-black">
                        <i class="mdi mdi-silverware-fork-knife text-xl"></i>
                    </div>
                    <h3 class="text-base font-semibold text-slate-50">Качество</h3>
                    <p class="mt-2 text-sm text-slate-300">
                        Ингредиенты и сборка заказа не должны проваливаться даже в
                        час пик. Если блюдо не выглядит аппетитно, оно не должно ехать к гостю.
                    </p>
                </article>

                <article class="rounded-2xl border border-white/10 bg-black/20 p-5">
                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-full bg-amber-400 text-black">
                        <i class="mdi mdi-lightning-bolt-outline text-xl"></i>
                    </div>
                    <h3 class="text-base font-semibold text-slate-50">Скорость</h3>
                    <p class="mt-2 text-sm text-slate-300">
                        Мы проектируем процессы так, чтобы путь от оформления до двери
                        был коротким и понятным. Без лишней ебанины внутри системы.
                    </p>
                </article>

                <article class="rounded-2xl border border-white/10 bg-black/20 p-5">
                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-full bg-amber-400 text-black">
                        <i class="mdi mdi-movie-open-outline text-xl"></i>
                    </div>
                    <h3 class="text-base font-semibold text-slate-50">Атмосфера</h3>
                    <p class="mt-2 text-sm text-slate-300">
                        У Gangsters есть настроение: от главной страницы до упаковки.
                        Заказ должен ощущаться как часть вечера, а не просто чек в пакете.
                    </p>
                </article>
            </div>
        </SecondaryContentBlock>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
            <article class="overflow-hidden rounded-[1.75rem] border border-amber-400/20 bg-[linear-gradient(180deg,rgba(251,191,36,0.08),rgba(255,255,255,0.03))] p-6">
                <p class="text-xs uppercase tracking-[0.26em] text-amber-200">
                    Атмосфера Gangsters
                </p>
                <p class="mt-4 text-2xl font-semibold leading-tight text-slate-50">
                    Мы не пытаемся быть «как все доставки». И в этом весь прикол.
                </p>
                <p class="mt-3 text-sm leading-relaxed text-slate-300">
                    Бренд держится на контрасте: тёмный фон, золото, аппетитные
                    фото, мягкое стекло и понятные сценарии взаимодействия.
                </p>
            </article>

            <SecondaryContentBlock
                title="Как строим сервис"
                subtitle="ОТ ЗАКАЗА ДО ДОСТАВКИ"
            >
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                        <p class="text-[11px] uppercase tracking-[0.22em] text-slate-400">01</p>
                        <p class="mt-2 font-medium text-slate-50">Понятный выбор</p>
                        <p class="mt-1 text-sm text-slate-300">
                            Каталог и акции помогают быстро собрать заказ без лишних кликов.
                        </p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                        <p class="text-[11px] uppercase tracking-[0.22em] text-slate-400">02</p>
                        <p class="mt-2 font-medium text-slate-50">Чёткая сборка</p>
                        <p class="mt-1 text-sm text-slate-300">
                            Кухня и логистика синхронизированы, чтобы не терять темп.
                        </p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                        <p class="text-[11px] uppercase tracking-[0.22em] text-slate-400">03</p>
                        <p class="mt-2 font-medium text-slate-50">Контроль качества</p>
                        <p class="mt-1 text-sm text-slate-300">
                            Следим за внешним видом, температурой и полнотой заказа.
                        </p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                        <p class="text-[11px] uppercase tracking-[0.22em] text-slate-400">04</p>
                        <p class="mt-2 font-medium text-slate-50">Нормальный финиш</p>
                        <p class="mt-1 text-sm text-slate-300">
                            У гостя должно остаться ощущение, что всё отработало красиво.
                        </p>
                    </div>
                </div>
            </SecondaryContentBlock>
        </div>
    </SecondaryPageLayout>
</template>
