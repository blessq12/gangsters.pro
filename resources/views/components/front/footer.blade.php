<footer class="relative overflow-hidden bg-[#111827] text-white">
    <div class="pointer-events-none absolute inset-0 opacity-[0.04]"
        style="background-image: radial-gradient(circle at 1px 1px, #fff 1px, transparent 0); background-size: 20px 20px;">
    </div>

    <div class="relative z-10 mx-auto max-w-7xl px-4 py-14 md:px-6 md:py-16">
        <div class="grid grid-cols-1 gap-10 md:grid-cols-12 md:gap-8">
            {{-- Бренд --}}
            <div class="md:col-span-4 space-y-5">
                <a href="{{ route('main.index') }}" class="inline-flex items-center gap-3 group">
                    <img src="/uploads/{{ $company->logo ? $company->logo : 'http://via.placeholder.com/50x50' }}"
                        alt="{{ $company->name }}"
                        class="h-11 w-11 rounded-lg object-contain bg-white/5 p-1.5 transition group-hover:bg-white/10">
                    <span class="text-lg font-semibold tracking-tight text-white/95">
                        {{ $company->name }}
                    </span>
                </a>
                @if ($company->description !== '')
                    <p class="max-w-sm text-sm leading-relaxed text-gray-400">
                        {{ $company->description }}
                    </p>
                @endif
                @if ($company->legals)
                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500">
                        <span>ИНН {{ $company->legals->inn }}</span>
                        <span>ОГРН {{ $company->legals->ogrn }}</span>
                    </div>
                @endif
            </div>

            {{-- Навигация --}}
            <div class="md:col-span-3">
                <h5 class="mb-4 text-xs font-semibold uppercase tracking-[0.14em] text-gray-500">Навигация</h5>
                <ul class="space-y-2.5 text-sm">
                    @if (Route::currentRouteName() !== 'main.index')
                        <li>
                            <a href="{{ route('main.index') }}"
                                class="text-gray-300 transition hover:text-white">Главная</a>
                        </li>
                    @endif
                    <li>
                        <a href="{{ route('main.about') }}"
                            class="text-gray-300 transition hover:text-white">О компании</a>
                    </li>
                    <li>
                        <a href="{{ route('main.purchaseAndDelivery') }}"
                            class="text-gray-300 transition hover:text-white">Оплата и доставка</a>
                    </li>
                    <li>
                        <a href="{{ route('main.contact') }}"
                            class="text-gray-300 transition hover:text-white">Контакты</a>
                    </li>
                    <li>
                        <a href="{{ route('main.seller') }}"
                            class="text-gray-300 transition hover:text-white">Реквизиты</a>
                    </li>
                </ul>
            </div>

            {{-- Документы --}}
            <div class="md:col-span-2">
                <h5 class="mb-4 text-xs font-semibold uppercase tracking-[0.14em] text-gray-500">Документы</h5>
                <ul class="space-y-2.5 text-sm">
                    <li>
                        <a href="{{ route('main.offer') }}"
                            class="text-gray-300 transition hover:text-white">Публичная оферта</a>
                    </li>
                    <li>
                        <a href="{{ route('main.terms') }}"
                            class="text-gray-300 transition hover:text-white">Пользовательское соглашение</a>
                    </li>
                    <li>
                        <a href="{{ route('main.privacy') }}"
                            class="text-gray-300 transition hover:text-white">Конфиденциальность</a>
                    </li>
                    <li>
                        <a href="{{ route('main.pdnConsent') }}"
                            class="text-gray-300 transition hover:text-white">Согласие на ПДн</a>
                    </li>
                    <li>
                        <a href="{{ route('main.cookies') }}"
                            class="text-gray-300 transition hover:text-white">Cookie</a>
                    </li>
                </ul>
            </div>

            {{-- Контакты и соцсети --}}
            <div class="md:col-span-3 space-y-5">
                <h5 class="mb-4 text-xs font-semibold uppercase tracking-[0.14em] text-gray-500">Связь</h5>
                <ul class="space-y-3 text-sm text-gray-300">
                    <li>
                        <a href="tel:{{ $company->phone }}" class="transition hover:text-white">
                            {{ $company->phone }}
                        </a>
                    </li>
                    <li>
                        <a href="mailto:{{ $company->email_address }}" class="transition hover:text-white break-all">
                            {{ $company->email_address }}
                        </a>
                    </li>
                    <li class="text-gray-400">
                        {{ $company->street }}, {{ $company->house }}
                    </li>
                </ul>

                @if ($company->vk || $company->inst)
                    <div class="flex flex-wrap gap-3 pt-1">
                        @if ($company->vk)
                            <a href="{{ $company->vk }}" target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 rounded-md border border-white/10 px-3 py-2 text-sm text-gray-300 transition hover:border-white/25 hover:text-white">
                                <img src="/vk.svg" alt="" class="h-4 w-4">
                                <span>ВКонтакте</span>
                            </a>
                        @endif
                        @if ($company->inst)
                            <a href="{{ $company->inst }}" target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 rounded-md border border-white/10 px-3 py-2 text-sm text-gray-300 transition hover:border-white/25 hover:text-white">
                                <i class="mdi mdi-instagram text-base"></i>
                                <span>Instagram*</span>
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-12 border-t border-white/10 pt-6 space-y-4">
            <div
                class="flex flex-col gap-3 text-xs text-gray-500 sm:flex-row sm:items-center sm:justify-between">
                <p>© {{ date('Y') }} {{ $company->name }}</p>
                <p class="sm:text-right">Доставка готовой еды в Томске</p>
            </div>

            @if ($company->inst)
                <p class="text-[11px] leading-relaxed text-gray-500 max-w-4xl">
                    * Meta Platforms Inc. (социальные сети Facebook и Instagram) — организация,
                    деятельность которой признана экстремистской и запрещена на территории
                    Российской Федерации.
                </p>
            @endif
        </div>
    </div>
</footer>
