<nav class="{{ !Request::is('/') ? 'position-sticky bg-light' : '' }}" style="{{ !Request::is('/') ? 'top: 0; z-index: 1000;' : '' }}">
    <div class="container">
        <div class="row align-items-center">
            <div class="col">
                <a href="{{ route('main.index') }}">
                    <div class="nav-logo">
                        <img src="/uploads/{{ $company->logo ? $company->logo : 'http://via.placeholder.com/50x50' }}" alt="{{ $company->name }}">
                        <span class="d-none d-lg-block">
                            {{ $company->name }}
                        </span>
                        <span class="d-lg-none">
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
            <div class="col d-lg-none d-flex justify-content-center">
                    <shedule
                        class="d-flex d-lg-none"
                        :shedule='@json($company->frontShedules())'
                    ></shedule>
            </div>
            <div class="col d-none d-lg-block justify-content-center">
                <ul class="nav-links" style="justify-content:{{!Request::is('/') ? 'end' : ''}}">
                    @if (!Request::is('/'))
                        <a href="{{ route('main.index') }}">
                            <li>{{ __('Главная') }}</li>
                        </a>
                    @endif
                    @foreach ($links as $link)
                    <a href="{{ route($link->route) }}" class="{{ Route::currentRouteName() === $link->route ? 'active' : '' }}">
                        <li>{{ __($link->name) }}</li>
                    </a>
                    @endforeach
                    
                </ul>
            </div>
            @if (true)
                <div class="col d-flex justify-content-end">
                    <shedule
                        class="d-none d-lg-flex"
                        :shedule='@json($company->frontShedules())'
                    ></shedule>
                    <ul class="shop-links d-lg-none">
                        <li>
                            <nav-button 
                                target="user"
                            >
                                Меню
                                <i class="fa fa-bars"></i>
                            </nav-button>
                        </li>
                    </ul>
                </div>
            @endif
        </div>
    </div>
    <modal></modal>
</nav>