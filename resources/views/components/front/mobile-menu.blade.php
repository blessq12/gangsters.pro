<div class="mobile-menu show">
    <div class="container">

        <div class="row mb-4">
            <div class="col">
                <div class="mobile-header">
                    <div class="d-flex align-items-center justify-content-between inner">
                        <h4 class="mb-0">Мобильное меню</h4>
                        <button class="btn-close" onclick="document.querySelector('.mobile-menu').classList.toggle('show')"></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col">
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