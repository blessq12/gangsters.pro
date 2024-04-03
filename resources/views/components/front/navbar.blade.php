<nav>
    <div class="container">
        <div class="row align-items-center">
            <div class="col">
                <a href="{{ route('main.index') }}">
                    <div class="nav-logo">
                        <img src="{{ $company->logo ? $company->logo->path : 'http://via.placeholder.com/50x50' }}" alt="{{ $company->name }}">
                        <span>
                            {{ $company->name }}
                        </span>
                    </div>
                </a>
            </div>
            <div class="col d-none d-lg-block">
                    <ul class="nav-links" style="justify-content:{{!Request::is('/') ? 'end' : ''}}">
                        @if (!Request::is('/'))    
                            <a href="{{ route('main.index') }}">
                                <li>{{ __('Главная') }}</li>
                            </a>
                        @endif
                        <a href="{{ route('main.about') }}">
                            <li>{{ __('О компании') }}</li>
                        </a>
                        <a href="{{ route('main.vacancy') }}">
                            <li>{{ __('Вакансии') }}</li>
                        </a>
                        <a href="{{ route('main.contact') }}">
                            <li>{{ __('Контакты') }}</li>
                        </a>
                    </ul>
            </div>
            @if (Request::is('/'))
            <div class="col">
                <ul class="shop-links">
                        <li>
                            <nav-button target="fav"></nav-button>
                        </li>
                        <li>
                            <nav-button target="cart"></nav-button>
                        </li>
                        <li class="d-none d-lg-block">
                            <nav-button target="user"></nav-button>
                        </li>
                        <li class="d-block d-lg-none">
                            <button type="button" class="btn btn-outline-primary" onclick="document.querySelector('.mobile-menu').classList.toggle('show')">
                                <i class="fa fa-bars"></i>
                            </button>
                        </li>
                    </ul>
                </div>
            @endif
            @if (!Request::is('/'))
                <div class="col d-lg-none d-block">
                    <ul class="shop-links">
                        <li class="d-block d-lg-none">
                            <button type="button" class="btn btn-outline-primary" onclick="document.querySelector('.mobile-menu').classList.toggle('show')">
                                <i class="fa fa-bars"></i>
                            </button>
                        </li>
                    </ul>
                </div>
            @endif
        </div>
    </div>
    <x-front.mobile-menu>
        
    </x-front.mobile-menu>
</nav>