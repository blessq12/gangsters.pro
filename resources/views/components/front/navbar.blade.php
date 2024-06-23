<nav>
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
            <div class="col d-block d-lg-none d-flex justify-content-center">
                <div class="shedule">
                    <div class="status {{ now()->format('H:i') > $currentDayShedule->close_time ? 'closed' : 'open' }}"></div>
                    @if (now()->format('H:i') > $currentDayShedule->close_time)
                        <span class="time">
                            Закрыто до {{ \Carbon\Carbon::parse($currentDayShedule->nextDayOpenTime())->format('H:i') }}
                        </span>
                    @else
                        <span class="time">
                            Открыто до {{ \Carbon\Carbon::parse($currentDayShedule->close_time)->format('H:i') }}
                        </span>
                    @endif
                </div>
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
            @if (Request::is('/'))
                <div class="col d-flex justify-content-end">
                    <div class="shedule d-none d-lg-flex">
                        
                    <div class="status {{ (now()->format('H:i') > $currentDayShedule->close_time || now()->format('H:i') < $currentDayShedule->open_time) ? 'closed' : 'open' }}"></div>
                    @if (now()->format('H:i') > $currentDayShedule->close_time || now()->format('H:i') < $currentDayShedule->open_time)
                        <span class="time">
                            Закрыто до {{ \Carbon\Carbon::parse($currentDayShedule->nextDayOpenTime())->format('H:i') }}
                        </span>
                    @else
                        <span class="time">
                            Открыто до {{ \Carbon\Carbon::parse($currentDayShedule->close_time)->format('H:i') }}
                        </span>
                    @endif
                </div>
                    <ul class="shop-links">
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