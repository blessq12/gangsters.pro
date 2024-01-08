<div class="sidebar">
    <h4>Навигация</h4>
    <ul>
        @foreach ($crmLinks as $link)
            <a href="{{ route($link->route) }}">
                <li class="{{ Request::segment(2) == explode('.', $link->route)[1] ? 'active' : '' }}">{{ $link->name }}</li>
            </a>
        @endforeach
    </ul>
</div>