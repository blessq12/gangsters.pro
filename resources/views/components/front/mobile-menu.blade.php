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
        <div class="row">
            <div class="col">
                <ul class="mobile-nav">
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