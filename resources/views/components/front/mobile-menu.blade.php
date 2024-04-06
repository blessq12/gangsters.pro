<div class="mobile-menu">
    <div class="container">

        <div class="row mb-4">
            <div class="col">
                <div class="mobile-header">
                    <div class="d-flex align-items-center justify-content-between text-light inner">
                        <span class="fs-4 fw-bold">Мобильное меню</span>
                        <i class="fa fa-times fa-2x" onclick="document.querySelector('.mobile-menu').classList.toggle('show')"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col text-light">
                <user-component></user-component>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <h4 class="text-light">Навигация</h4>
                <hr class="border-warning m-0">
                <ul class="mobile-nav">
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
        </div>
    </div>
</div>