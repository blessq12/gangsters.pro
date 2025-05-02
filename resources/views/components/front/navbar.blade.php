<nav
    class="{{ !Request::is('/') ? 'sticky top-0 bg-white/95 backdrop-blur-sm z-[1000] transition-all duration-300' : '' }} shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
            <div class="flex-shrink-0 transition-transform duration-300 hover:scale-105">
                <a href="{{ route('main.index') }}" class="group">
                    <div class="flex items-center">
                        <img src="/uploads/{{ $company->logo ? $company->logo : 'http://via.placeholder.com/50x50' }}"
                            alt="{{ $company->name }}"
                            class="h-12 w-auto rounded-lg shadow-sm group-hover:shadow-md transition-all duration-300">
                        <span
                            class="hidden lg:block ml-3 font-semibold text-gray-800 group-hover:text-blue-600 transition-colors duration-300">
                            {{ $company->name }}
                        </span>
                        <span class="lg:hidden font-semibold text-gray-800 ml-2">
                            @php
                                $name = $company->name;
                                $name = explode(' ', $name);
                                $result = '';
                                foreach ($name as $word) {
                                    $result .= strtoupper(substr($word, 0, 1));
                                }
                            @endphp
                            {{ $result }}
                        </span>
                    </div>
                </a>
            </div>
            <div class="lg:hidden flex justify-center">
                <shedule class="flex lg:hidden"></shedule>
            </div>
            <div class="hidden lg:block">
                <ul class="flex space-x-6 {{ !Request::is('/') ? 'justify-end' : '' }}">
                    @if (!Request::is('/'))
                        <a href="{{ route('main.index') }}">
                            <li
                                class="relative py-2 text-gray-700 hover:text-blue-600 transition-colors duration-300 after:content-[''] after:absolute after:bottom-0 after:left-0 after:w-0 after:h-0.5 after:bg-blue-600 after:transition-all hover:after:w-full">
                                {{ __('Главная') }}
                            </li>
                        </a>
                    @endif
                    @foreach ($links as $link)
                        <a href="{{ route($link->route) }}"
                            class="{{ Route::currentRouteName() === $link->route ? 'text-blue-600' : 'text-gray-700' }}">
                            <li
                                class="relative py-2 transition-colors duration-300 hover:text-blue-600 after:content-[''] after:absolute after:bottom-0 after:left-0 after:w-0 after:h-0.5 after:bg-blue-600 after:transition-all hover:after:w-full {{ Route::currentRouteName() === $link->route ? 'after:w-full' : '' }}">
                                {{ __($link->name) }}
                            </li>
                        </a>
                    @endforeach
                </ul>
            </div>
            @if (Request::is('/'))
                <div class="flex items-center justify-end">
                    <shedule class="hidden lg:flex"></shedule>
                    <ul class="flex items-center space-x-4 ml-8">
                        <li>
                            <nav-button target="user"
                                class="group flex items-center space-x-2 px-4 py-2 rounded-lg border border-gray-200 hover:border-blue-400 hover:bg-blue-50 transition-all duration-300">
                                <span
                                    class="hidden lg:block text-gray-700 group-hover:text-blue-600 transition-colors duration-300">
                                    Меню
                                </span>
                                <i
                                    class="fa fa-bars text-gray-700 group-hover:text-blue-600 transition-colors duration-300"></i>
                            </nav-button>
                        </li>
                    </ul>
                </div>
            @else
                <div class="lg:hidden flex items-center justify-end">
                    <shedule class="hidden lg:flex"></shedule>
                    <ul>
                        <li>
                            <secondary-menu-button
                                class="p-2 rounded-lg hover:bg-gray-100 transition-colors duration-300"></secondary-menu-button>
                        </li>
                    </ul>
                </div>
            @endif
        </div>
    </div>
    <modal></modal>
</nav>
