<footer class="bg-gradient-to-br from-gray-900 to-gray-800 text-white py-16 relative overflow-hidden">
    <div class="absolute inset-0 bg-grid-white/[0.05] bg-[size:16px_16px]"></div>
    <div class=" mx-auto max-w-7xl  px-4 md:px-6  relative z-10">
        <!-- Первая строка: три колонки -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 mb-16">
            <!-- О компании -->
            <div class="space-y-6">
                <a href="{{ route('main.index') }}" class="text-white inline-block group">
                    <div class="flex items-center space-x-4 transform transition duration-300 group-hover:translate-x-2">
                        <div class="bg-white/10 p-3 rounded-xl backdrop-blur-sm shadow-xl">
                            <img src="/uploads/{{ $company->logo ? $company->logo : 'http://via.placeholder.com/50x50' }}"
                                alt="{{ $company->name }}" class="w-12 h-12 object-contain filter drop-shadow-lg">
                        </div>
                        <span class="text-lg sm:text-xl font-bold tracking-tight text-white/95">
                            {{ $company->name }}
                        </span>
                    </div>
                </a>
                @if ($company->description !== '')
                    <div class="mt-4">
                        <p class="text-sm sm:text-base text-gray-300 leading-relaxed">
                            {{ $company->description }}
                        </p>
                    </div>
                @endif
            </div>

            <!-- Ссылки -->
            <div class="space-y-6">
                <h5
                    class="text-lg sm:text-xl font-bold tracking-tight relative inline-block after:content-[''] after:absolute after:w-1/2 after:h-1 after:-bottom-2 after:left-0 after:bg-blue-500/80 text-white/95">
                    Ссылки</h5>
                <ul class="space-y-2">
                    @if (Route::currentRouteName() !== 'main.index')
                        <a href="{{ route('main.index') }}" class="block">
                            <li
                                class="flex items-center space-x-2 text-gray-300 hover:text-white transition-colors duration-300 py-2 hover:translate-x-2 transform text-sm sm:text-base">
                                <i class="mdi mdi-home text-xl"></i>
                                <span>Главная</span>
                            </li>
                        </a>
                    @endif
                    <a href="{{ route('main.about') }}" class="block">
                        <li
                            class="flex items-center space-x-2 text-gray-300 hover:text-white transition-colors duration-300 py-2 hover:translate-x-2 transform text-sm sm:text-base">
                            <i class="mdi mdi-information text-xl"></i>
                            <span>О компании</span>
                        </li>
                    </a>

                    <a href="{{ route('main.purchaseAndDelivery') }}" class="block">
                        <li
                            class="flex items-center space-x-2 text-gray-300 hover:text-white transition-colors duration-300 py-2 hover:translate-x-2 transform text-sm sm:text-base">
                            <i class="mdi mdi-truck-delivery text-xl"></i>
                            <span>Оплата и доставка</span>
                        </li>
                    </a>
                    <a href="{{ route('main.contact') }}" class="block">
                        <li
                            class="flex items-center space-x-2 text-gray-300 hover:text-white transition-colors duration-300 py-2 hover:translate-x-2 transform text-sm sm:text-base">
                            <i class="mdi mdi-contacts text-xl"></i>
                            <span>Контакты</span>
                        </li>
                    </a>
                </ul>
            </div>

            <!-- Социальные сети -->
            @if ($company->vk || $company->inst)
                <div class="space-y-6">
                    <h5
                        class="text-lg sm:text-xl font-bold tracking-tight relative inline-block after:content-[''] after:absolute after:w-1/2 after:h-1 after:-bottom-2 after:left-0 after:bg-blue-500/80 text-white/95">
                        Соц сети</h5>
                    <ul class="space-y-4">
                        @if ($company->vk)
                            <a href="{{ $company->vk }}" target="_blank" class="block group">
                                <li
                                    class="flex items-center space-x-3 rounded-lg transition duration-300 hover:bg-white/5 backdrop-blur-sm">
                                    <div
                                        class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-500/10 text-blue-400 group-hover:bg-blue-500 group-hover:text-white transition duration-300 shadow-lg">
                                        <img src="/vk.svg" class="w-6 h-6">
                                    </div>
                                    <span class="text-sm sm:text-base text-gray-300 group-hover:text-white">Группа
                                        Вконтакте</span>
                                </li>
                            </a>
                        @endif
                        @if ($company->inst)
                            <a href="{{ $company->inst }}" target="_blank" class="block group">
                                <li
                                    class="flex items-center space-x-3 rounded-lg transition duration-300 hover:bg-white/5 backdrop-blur-sm">
                                    <div
                                        class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-500/10 text-blue-400 group-hover:bg-blue-500 group-hover:text-white transition duration-300 shadow-lg">
                                        <i class="mdi mdi-instagram text-2xl"></i>
                                    </div>
                                    <span class="text-sm sm:text-base text-gray-300 group-hover:text-white">Профиль
                                        Instagram</span>
                                </li>
                            </a>
                        @endif
                    </ul>
                </div>
            @endif
        </div>

        <!-- Вторая строка: контакты в ряд -->
        <div class="border-t border-white/10 pt-12 pb-8">
            <div
                class="flex flex-col md:flex-row flex-wrap justify-center md:justify-between items-start md:items-center gap-8">
                <a href="tel:{{ $company->phone }}"
                    class="flex items-center space-x-3 text-gray-300 hover:text-white transition-colors duration-300 text-sm sm:text-base">
                    <div class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-500/10 text-blue-400">
                        <i class="mdi mdi-phone text-xl"></i>
                    </div>
                    <span>{{ $company->phone }}</span>
                </a>

                <a href="https://yandex.ru/maps/67/tomsk/?ll=84.986330%2C56.513423&mode=routes&rtext=~56.513356%2C84.986301&rtt=auto&ruri=~ymapsbm1%3A%2F%2Forg%3Foid%3D82888444717&z=16.7"
                    class="flex items-center space-x-3 text-gray-300 hover:text-white transition-colors duration-300 text-sm sm:text-base">
                    <div class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-500/10 text-blue-400">
                        <i class="mdi mdi-map-marker text-xl"></i>
                    </div>
                    <span>{{ $company->street }}, {{ $company->house }}</span>
                </a>

                <a href="mailto:{{ $company->email_address }}"
                    class="flex items-center space-x-3 text-gray-300 hover:text-white transition-colors duration-300 text-sm sm:text-base">
                    <div class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-500/10 text-blue-400">
                        <i class="mdi mdi-email text-xl"></i>
                    </div>
                    <span>{{ $company->email_address }}</span>
                </a>
            </div>
        </div>

        <!-- Подвал футера -->
        <div class="border-t border-white/10 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 text-xs sm:text-sm text-gray-400">
                <div class="flex flex-wrap items-center gap-x-2 gap-y-2">
                    <p class="flex items-center space-x-2">
                        <i class="mdi mdi-identifier text-xs text-blue-400"></i>
                        <span>ИНН: {{ $company->legals->inn }}</span>
                    </p>
                    <p class="flex items-center space-x-2">
                        <i class="mdi mdi-card-account-details-outline text-xs text-blue-400"></i>
                        <span>ОГРН: {{ $company->legals->ogrn }}</span>
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-4">
                    <a href="{{ route('main.privacy') }}"
                        class="flex items-center space-x-2 hover:text-white transition-colors duration-300 text-sm sm:text-base">
                        <i class="mdi mdi-shield-lock text-xs"></i>
                        <span>Политика конфиденциальности</span>
                    </a>
                    <p class="flex items-center space-x-2">
                        <i class="mdi mdi-copyright text-xs"></i>
                        <span>{{ date('Y') }} {{ $company->name }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</footer>
