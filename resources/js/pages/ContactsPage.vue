<template>
    <SecondaryPageLayout
        title="Контакты"
        eyebrow="Связаться с нами"
        description="Нужен заказ, уточнение по доставке или партнёрский вопрос — вот все каналы связи, собранные в одном месте без бардака."
        :breadcrumbs="['Главная', 'Контакты']"
        hero-image="/images/banners/banner3.jpeg"
        :stats="[
            { label: 'Режим', value: '10:00 - 02:00' },
            { label: 'Ответ', value: 'Быстро в Telegram' },
            { label: 'Формат', value: 'Ежедневная доставка' },
        ]"
    >
        <div class="grid gap-4 md:grid-cols-3">
            <article class="rounded-[1.75rem] border border-white/10 bg-[rgba(255,255,255,0.04)] p-5 shadow-[0_16px_50px_rgba(0,0,0,0.35)]">
                <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-full bg-amber-400 text-black">
                    <i class="mdi mdi-phone-outline text-xl"></i>
                </div>
                <p class="text-xs uppercase tracking-[0.22em] text-slate-400">Телефон</p>
                <p class="mt-2 text-lg font-semibold text-slate-50">+7 (XXX) XXX-XX-XX</p>
                <p class="mt-2 text-sm text-slate-300">
                    Для заказов, уточнений по доставке и быстрых вопросов по меню.
                </p>
            </article>

            <article class="rounded-[1.75rem] border border-white/10 bg-[rgba(255,255,255,0.04)] p-5 shadow-[0_16px_50px_rgba(0,0,0,0.35)]">
                <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-full bg-amber-400 text-black">
                    <i class="mdi mdi-send-outline text-xl"></i>
                </div>
                <p class="text-xs uppercase tracking-[0.22em] text-slate-400">Telegram</p>
                <p class="mt-2 text-lg font-semibold text-slate-50">@gangsters</p>
                <p class="mt-2 text-sm text-slate-300">
                    Самый быстрый канал для связи, поддержки и получения актуальных акций.
                </p>
            </article>

            <article class="rounded-[1.75rem] border border-white/10 bg-[rgba(255,255,255,0.04)] p-5 shadow-[0_16px_50px_rgba(0,0,0,0.35)]">
                <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-full bg-amber-400 text-black">
                    <i class="mdi mdi-email-outline text-xl"></i>
                </div>
                <p class="text-xs uppercase tracking-[0.22em] text-slate-400">Эл. почта</p>
                <p class="mt-2 text-lg font-semibold text-slate-50">hello@gangsters.ru</p>
                <p class="mt-2 text-sm text-slate-300">
                    Для партнёрств, предложений и всего, что не требует ответа за минуту.
                </p>
            </article>
        </div>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(280px,0.85fr)]">
            <SecondaryContentBlock
                title="Где мы находимся"
                subtitle="БАЗА КУХНИ"
            >
                <p>
                    Основная кухня расположена по адресу: г. Город, ул. Примерная, д. 1.
                    Здесь готовятся и собираются заказы для доставки по городу и ближайшим районам.
                </p>
                <p>
                    Самовывоз пока не предусмотрен. Мы сфокусированы именно на доставке,
                    чтобы поддерживать стабильный сервис и единый стандарт качества.
                </p>

                <div class="mt-4 rounded-2xl border border-white/10 bg-black/20 p-4">
                    <p class="text-[11px] uppercase tracking-[0.22em] text-slate-400">
                        Зона покрытия
                    </p>
                    <p class="mt-2 text-sm text-slate-200">
                        Город и ближайший пригород. Точная доступность всегда проверяется по адресу в корзине.
                    </p>
                </div>
            </SecondaryContentBlock>

            <article class="overflow-hidden rounded-[1.75rem] border border-amber-400/20 bg-[linear-gradient(180deg,rgba(251,191,36,0.1),rgba(255,255,255,0.03))] p-6">
                <p class="text-xs uppercase tracking-[0.26em] text-amber-200">
                    Режим работы
                </p>
                <p class="mt-4 text-3xl font-semibold text-slate-50">10:00 - 02:00</p>
                <p class="mt-3 text-sm leading-relaxed text-slate-300">
                    Заказы принимаем ежедневно. Ночью работаем в том самом режиме,
                    за который нас и любят: когда город уже устал, мы ещё готовим.
                </p>
                <div class="mt-5 space-y-2 text-sm text-slate-200">
                    <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-black/25 px-4 py-3">
                        <span>Поддержка</span>
                        <span class="font-medium text-amber-200">ежедневно</span>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-black/25 px-4 py-3">
                        <span>Пиковые часы</span>
                        <span class="font-medium text-amber-200">18:00 - 22:00</span>
                    </div>
                </div>
            </article>
        </div>

        <SecondaryContentBlock
            title="Как лучше связаться"
            subtitle="КАК С НАМИ СВЯЗАТЬСЯ"
        >
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl border border-white/10 bg-black/20 p-5">
                    <p class="text-[11px] uppercase tracking-[0.22em] text-slate-400">01</p>
                    <p class="mt-2 font-medium text-slate-50">По заказу</p>
                    <p class="mt-1 text-sm text-slate-300">
                        Звонок или Telegram — самый короткий путь, если вопрос срочный.
                    </p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/20 p-5">
                    <p class="text-[11px] uppercase tracking-[0.22em] text-slate-400">02</p>
                    <p class="mt-2 font-medium text-slate-50">По сотрудничеству</p>
                    <p class="mt-1 text-sm text-slate-300">
                        Лучше писать на email, чтобы не потерялись детали и контакты.
                    </p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/20 p-5">
                    <p class="text-[11px] uppercase tracking-[0.22em] text-slate-400">03</p>
                    <p class="mt-2 font-medium text-slate-50">По акциям и новостям</p>
                    <p class="mt-1 text-sm text-slate-300">
                        Telegram удобнее всего: там быстрее всего появляются обновления.
                    </p>
                </div>
            </div>
        </SecondaryContentBlock>
    </SecondaryPageLayout>
</template>
