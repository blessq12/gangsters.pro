<nav>
    <div class="container">
        <div class="row align-items-center">
            <div class="col">
                <a href="{{ route('main.index') }}">
                    <div class="nav-logo">
                        <img src="/uploads/{{ $company->logo ? $company->logo : 'http://via.placeholder.com/50x50' }}" alt="{{ $company->name }}">
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
                    @foreach ($links as $link)
                    <a href="{{ route($link->route) }}" class="{{ Route::currentRouteName() === $link->route ? 'active' : '' }}">
                        <li>{{ __($link->name) }}</li>
                    </a>
                    @endforeach
                    
                </ul>
            </div>
            @if (Request::is('/'))
            <div class="col">
                <ul class="shop-links">
                    <li>
                        <nav-button 
                            target="user"
                        ></nav-button>
                    </li>
                </ul>
            </div>
            @endif
        </div>
    </div>
    <modal></modal>
</nav>