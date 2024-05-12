<footer>
    <div class="container">
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
            <div class="col">
                <a href="{{ route('main.index') }}">
                    <div class="footer-logo">
                        <img src="/uploads/{{ $company->logo ? $company->logo : 'http://via.placeholder.com/50x50' }}" alt="{{ $company->name }}">
                        <span>
                            {{ $company->name }}
                        </span>
                    </div>
                </a>
                @if ($company->description !== '')
                        <div class="my-2">
                            <p>
                                {{ $company->description }}
                            </p>
                        </div>
                    @endif
            </div>

            <div class="col">
                <h5>Контакты</h5>
                <ul>
                    <a href="tel:{{ $company->phone }}">
                        <li>
                            <i class="fa fa-phone"></i>
                            <span>{{ $company->phone }}</span>
                        </li>
                    </a>
                    <a href="tel:{{ $company->phone_additional }}">
                        <li>
                            <i class="fa fa-phone"></i>
                            <span>{{ $company->phone_additional }}</span>
                        </li>
                    </a>
                    <a href="https://yandex.ru/maps/67/tomsk/?ll=84.986330%2C56.513423&mode=routes&rtext=~56.513356%2C84.986301&rtt=auto&ruri=~ymapsbm1%3A%2F%2Forg%3Foid%3D82888444717&z=16.7">
                        <li>
                            <i class="fa fa-building"></i>
                            <span>{{ $company->street }}, {{ $company->house }}</span>
                        </li>
                    </a>
                    <a href="mailto:{{ $company->email_address }}">
                        <li>
                            <i class="fa fa-envelope"></i>
                            <span>{{ $company->email_address }}</span>
                        </li>
                    </a>
                </ul>
            </div>

            <div class="col">
                <h5>Ссылки</h5>
                <ul class="links">
                    @if (Route::currentRouteName() !== 'main.index')
                        <a href="{{ route('main.index') }}">
                            <li>
                                Главная
                            </li>
                        </a>
                    @endif
                    <a href="{{ route('main.about') }}">
                        <li>
                            О компании
                        </li>
                    </a>
                    <a href="{{ route('main.vacancy') }}">
                        <li>
                            Вакансии
                        </li>
                    </a>
                    <a href="{{ route('main.purchaseAndDelivery') }}">
                        <li>
                            Оплата и доставка
                        </li>
                    </a>
                    <a href="{{ route('main.contact') }}">
                        <li>
                            Контакты
                        </li>
                    </a>
                </ul>
            </div>

            <div class="col">
                <h5>Соц сети</h5>
            </div>
        </div>
    </div>
    <div class="copyright">
        <div class="container">
            <div class="row py-3">
                <div class="col">
                    <a href="{{ route('main.privacy') }}">Политика конфиденциальности</a>
                </div>
            </div>
        </div>
    </div>
</footer>